(function () {
    "use strict";

    /*
     |--------------------------------------------------------------------------
     | Progress bar navigasi
     |--------------------------------------------------------------------------
     | Garis tipis di atas layar saat pindah halaman / submit form, tanpa
     | menutupi konten seperti overlay layar penuh.
     */
    const progress = document.getElementById("page-progress");

    function startProgress() {
        if (progress) progress.classList.add("page-progress--active");
    }

    function stopProgress() {
        if (progress) progress.classList.remove("page-progress--active");
    }

    // Saat kembali lewat tombol Back (bfcache), pulihkan field/tombol yang tadi dinonaktifkan.
    window.addEventListener("pageshow", function () {
        stopProgress();
        document.querySelectorAll("[data-auto-disabled]").forEach(function (field) {
            field.disabled = false;
            field.classList.remove("opacity-75");
            delete field.dataset.autoDisabled;
        });
    });

    document.addEventListener("click", function (event) {
        const link = event.target.closest("a[href]");
        if (
            !link ||
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            (link.target && link.target !== "_self") ||
            link.hasAttribute("download") ||
            link.dataset.download !== undefined ||
            link.classList.contains("pointer-events-none")
        ) {
            return;
        }

        const href = link.getAttribute("href");
        if (!href || href.startsWith("#") || href.startsWith("javascript:")) return;
        if (link.origin !== window.location.origin) return;

        startProgress();
    });

    /*
     |--------------------------------------------------------------------------
     | Form: konfirmasi, cegah double submit, URL filter yang bersih
     |--------------------------------------------------------------------------
     */
    document.addEventListener(
        "submit",
        function (event) {
            const form = event.target;

            if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            if (event.defaultPrevented) return;

            if ((form.method || "get").toLowerCase() === "get") {
                // Jangan kirim parameter kosong supaya URL filter tetap rapi.
                form.querySelectorAll("input[name], select[name]").forEach(function (field) {
                    if (!field.value && !field.disabled) {
                        field.disabled = true;
                        field.dataset.autoDisabled = "1";
                    }
                });
            } else {
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    button.disabled = true;
                    button.dataset.autoDisabled = "1";
                    button.classList.add("opacity-75");
                });
            }

            startProgress();
        },
        false
    );

    document.addEventListener("change", function (event) {
        const field = event.target;
        if (field.matches("[data-auto-submit]") && field.form) {
            // Kelas bergantung pada jenjang, jadi reset saat jenjang berganti.
            if (field.name === "jenjang_id" && field.form.elements.kelas) {
                field.form.elements.kelas.value = "";
            }
            field.form.requestSubmit();
        }
    });

    /*
     |--------------------------------------------------------------------------
     | Pencarian anggota (autocomplete)
     |--------------------------------------------------------------------------
     | <div data-anggota-picker data-url="..." data-tipe="siswa">
     |     <input type="hidden" name="anggota_id" data-anggota-id>
     |     <input type="text" data-anggota-search list="...">
     |     <datalist id="..."></datalist>
     | </div>
     */
    const optionCache = new Map();

    function fetchAnggota(picker, keyword) {
        const params = new URLSearchParams();
        if (keyword) params.set("q", keyword);
        if (picker.dataset.tipe) params.set("tipe", picker.dataset.tipe);
        const url = picker.dataset.url + "?" + params.toString();

        if (optionCache.has(url)) return Promise.resolve(optionCache.get(url));

        return fetch(url, { headers: { Accept: "application/json" } })
            .then(function (response) {
                return response.ok ? response.json() : Promise.reject(response);
            })
            .then(function (items) {
                optionCache.set(url, items);
                return items;
            });
    }

    function setPickerValue(picker, item) {
        const hidden = picker.querySelector("[data-anggota-id]");
        const search = picker.querySelector("[data-anggota-search]");
        const changed = hidden.value !== String(item ? item.id : "");

        const tipeInput = picker.querySelector("[data-anggota-tipe-input]");

        hidden.value = item ? item.id : "";
        hidden.dataset.tipe = item ? item.tipe : "";
        if (tipeInput) tipeInput.value = hidden.dataset.tipe;
        if (item && search.value !== item.label) search.value = item.label;

        search.classList.toggle("border-success", Boolean(item));
        if (changed) hidden.dispatchEvent(new Event("change", { bubbles: true }));
    }

    function initPicker(picker) {
        if (picker.dataset.ready) return;
        picker.dataset.ready = "1";

        const search = picker.querySelector("[data-anggota-search]");
        const list = picker.querySelector("datalist");
        let items = [];
        let timer = null;

        function render(results) {
            items = results;
            list.innerHTML = "";
            results.forEach(function (item) {
                const option = document.createElement("option");
                option.value = item.label;
                list.appendChild(option);
            });
            sync();
        }

        function sync() {
            const selected = items.find(function (item) {
                return item.label === search.value;
            });
            if (selected) {
                setPickerValue(picker, selected);
            } else if (!search.value) {
                setPickerValue(picker, null);
            } else {
                const hidden = picker.querySelector("[data-anggota-id]");
                if (hidden.value) setPickerValue(picker, null);
            }
        }

        function load() {
            const keyword = search.value.split(" - ")[0].trim();
            search.classList.add("opacity-70");
            fetchAnggota(picker, keyword)
                .then(render)
                .catch(function () {})
                .finally(function () {
                    search.classList.remove("opacity-70");
                });
        }

        search.addEventListener("focus", load, { once: true });
        search.addEventListener("input", function () {
            sync();
            clearTimeout(timer);
            timer = setTimeout(load, 250);
        });
        search.addEventListener("change", sync);

        const form = picker.closest("form");
        if (form) {
            form.addEventListener("submit", function (event) {
                if (!picker.querySelector("[data-anggota-id]").value) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    search.setCustomValidity("Pilih nama dari daftar saran yang muncul.");
                    search.reportValidity();
                    search.addEventListener("input", function () {
                        search.setCustomValidity("");
                    }, { once: true });
                }
            }, true);
        }
    }

    document.querySelectorAll("[data-anggota-picker]").forEach(initPicker);

    /*
     |--------------------------------------------------------------------------
     | Field khusus guru (gula darah & kolesterol)
     |--------------------------------------------------------------------------
     */
    function toggleGuruFields(form) {
        const hidden = form.querySelector("[data-anggota-id]");
        const isGuru = hidden && hidden.dataset.tipe === "guru";

        form.querySelectorAll("[data-guru-field]").forEach(function (field) {
            field.classList.toggle("hidden", !isGuru);
            field.querySelectorAll("input").forEach(function (input) {
                input.disabled = !isGuru;
            });
        });
    }

    document.addEventListener("change", function (event) {
        if (event.target.matches("[data-anggota-id]") && event.target.form) {
            toggleGuruFields(event.target.form);
        }
    });
    document.querySelectorAll("form").forEach(toggleGuruFields);

    /*
     |--------------------------------------------------------------------------
     | Modal: isi anggota otomatis, fokus input pertama, buka ulang saat error
     |--------------------------------------------------------------------------
     */
    document.addEventListener("click", function (event) {
        const trigger = event.target.closest("[data-pick-anggota]");
        if (!trigger) return;

        const modal = document.querySelector(trigger.dataset.twTarget);
        const picker = modal && modal.querySelector("[data-anggota-picker]");
        if (!picker) return;

        initPicker(picker);
        setPickerValue(picker, {
            id: trigger.dataset.anggotaId,
            tipe: trigger.dataset.anggotaTipe,
            label: trigger.dataset.anggotaLabel,
        });
    });

    document.querySelectorAll(".modal").forEach(function (modal) {
        modal.addEventListener("shown.tw.modal", function () {
            const first = modal.querySelector(
                "[data-anggota-search]:not([readonly]), input:not([type=hidden]):not([disabled]), textarea"
            );
            const search = modal.querySelector("[data-anggota-search]");
            const target = search && search.value ? modal.querySelector("textarea, input[type=number]") : first;
            if (target) target.focus();
        });
    });

    const reopen = document.querySelector("[data-open-on-load]");
    if (reopen && window.tailwind && window.tailwind.Modal) {
        window.tailwind.Modal.getOrCreateInstance(reopen).show();
    }

    /*
     |--------------------------------------------------------------------------
     | Hitung BMI langsung saat berat/tinggi diisi
     |--------------------------------------------------------------------------
     */
    document.querySelectorAll("[data-bmi-preview]").forEach(function (preview) {
        const form = preview.closest("form");
        const berat = form.querySelector('[name="berat_badan"]');
        const tinggi = form.querySelector('[name="tinggi_badan"]');

        function update() {
            const b = parseFloat(berat.value);
            const t = parseFloat(tinggi.value) / 100;
            if (!b || !t) {
                preview.textContent = "-";
                return;
            }
            const bmi = b / (t * t);
            const kategori = bmi < 18.5 ? "Kurus" : bmi < 25 ? "Normal" : bmi < 30 ? "Gemuk" : "Obesitas";
            preview.textContent = bmi.toFixed(1) + " (" + kategori + ")";
        }

        berat.addEventListener("input", update);
        tinggi.addEventListener("input", update);
        update();
    });
})();
