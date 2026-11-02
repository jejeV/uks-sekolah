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
        document.querySelectorAll("[data-submitter-carrier]").forEach(function (carrier) {
            carrier.remove();
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
                // Tombol yang dinonaktifkan tidak ikut terkirim, jadi salin name/value tombol yang diklik.
                const submitter = event.submitter;
                if (submitter && submitter.name) {
                    const carrier = document.createElement("input");
                    carrier.type = "hidden";
                    carrier.name = submitter.name;
                    carrier.value = submitter.value;
                    carrier.dataset.submitterCarrier = "1";
                    form.appendChild(carrier);
                }

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

    // Tombol "Ubah": isi form di modal tujuan dari data JSON tombol tersebut.
    document.addEventListener("click", function (event) {
        const trigger = event.target.closest("[data-fill-form]");
        if (!trigger) return;

        const modal = document.querySelector(trigger.dataset.twTarget);
        const form = modal && modal.querySelector("form");
        if (!form) return;

        const data = JSON.parse(trigger.dataset.fillForm);
        if (trigger.dataset.formAction) form.action = trigger.dataset.formAction;

        Object.keys(data).forEach(function (key) {
            if (key === "anggota") {
                const picker = form.querySelector("[data-anggota-picker]");
                if (picker) {
                    initPicker(picker);
                    setPickerValue(picker, data.anggota);
                }
                return;
            }
            const field = form.elements[key];
            if (field) field.value = data[key] === null ? "" : data[key];
        });
    });

    // Chip keluhan cepat: tambahkan teks ke textarea tujuan.
    document.addEventListener("click", function (event) {
        const chip = event.target.closest("[data-append-to]");
        if (!chip) return;

        const target = document.querySelector(chip.dataset.appendTo);
        const text = chip.textContent.trim();
        if (!target) return;

        target.value = target.value.trim() ? target.value.trim().replace(/,$/, "") + ", " + text.toLowerCase() : text;
        target.focus();
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
     | Notifikasi sukses hilang otomatis
     |--------------------------------------------------------------------------
     */
    document.querySelectorAll("[data-auto-dismiss]").forEach(function (toast) {
        let timer;
        const dismiss = function () {
            toast.classList.add("opacity-0", "translate-x-4");
            setTimeout(function () {
                toast.remove();
            }, 300);
        };
        const start = function () {
            timer = setTimeout(dismiss, parseInt(toast.dataset.autoDismiss, 10) || 5000);
        };

        toast.addEventListener("mouseenter", function () {
            clearTimeout(timer);
        });
        toast.addEventListener("mouseleave", start);
        start();
    });

    /*
     |--------------------------------------------------------------------------
     | Pencarian cepat siswa/guru di top bar (tekan "/" untuk fokus)
     |--------------------------------------------------------------------------
     */
    const globalSearch = document.querySelector("[data-global-search]");

    if (globalSearch) {
        const input = globalSearch.querySelector("input");
        const results = globalSearch.querySelector("[role=listbox]");
        let items = [];
        let active = -1;
        let timer = null;

        const close = function () {
            results.classList.add("hidden");
            input.setAttribute("aria-expanded", "false");
            input.removeAttribute("aria-activedescendant");
            active = -1;
        };

        const highlight = function () {
            results.querySelectorAll("[role=option]").forEach(function (option, index) {
                const selected = index === active;
                option.classList.toggle("bg-slate-100", selected);
                option.classList.toggle("dark:bg-darkmode-400", selected);
                option.setAttribute("aria-selected", selected ? "true" : "false");
                if (selected) {
                    input.setAttribute("aria-activedescendant", option.id);
                    option.scrollIntoView({ block: "nearest" });
                }
            });
        };

        const render = function (list) {
            items = list;
            active = list.length ? 0 : -1;
            results.innerHTML = "";

            if (!list.length) {
                const empty = document.createElement("div");
                empty.className = "px-4 py-3 text-slate-500 text-sm";
                empty.textContent = "Tidak ada siswa/guru yang cocok.";
                results.appendChild(empty);
            }

            list.forEach(function (item, index) {
                const [nama, ...rest] = item.label.split(" - ");
                const option = document.createElement("a");
                option.href = item.url;
                option.id = "global-search-option-" + index;
                option.setAttribute("role", "option");
                option.className = "block px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-darkmode-400";

                const title = document.createElement("div");
                title.className = "font-medium truncate";
                title.textContent = nama;
                const detail = document.createElement("div");
                detail.className = "text-xs text-slate-500 truncate";
                detail.textContent = rest.join(" - ");

                option.append(title, detail);
                option.addEventListener("mouseenter", function () {
                    active = index;
                    highlight();
                });
                results.appendChild(option);
            });

            results.classList.remove("hidden");
            input.setAttribute("aria-expanded", "true");
            highlight();
        };

        input.addEventListener("input", function () {
            clearTimeout(timer);
            const keyword = input.value.trim();
            if (!keyword) {
                close();
                return;
            }
            timer = setTimeout(function () {
                fetchAnggota(globalSearch, keyword).then(render).catch(function () {});
            }, 200);
        });

        input.addEventListener("keydown", function (event) {
            if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                event.preventDefault();
                if (!items.length) return;
                active = (active + (event.key === "ArrowDown" ? 1 : -1) + items.length) % items.length;
                highlight();
            } else if (event.key === "Enter") {
                event.preventDefault();
                if (items[active]) {
                    startProgress();
                    window.location.href = items[active].url;
                }
            } else if (event.key === "Escape") {
                close();
                input.blur();
            }
        });

        input.addEventListener("focus", function () {
            if (items.length && input.value.trim()) {
                results.classList.remove("hidden");
                input.setAttribute("aria-expanded", "true");
            }
        });

        document.addEventListener("click", function (event) {
            if (!globalSearch.contains(event.target)) close();
        });

        document.addEventListener("keydown", function (event) {
            const tag = (event.target.tagName || "").toLowerCase();
            const typing = ["input", "textarea", "select"].includes(tag) || event.target.isContentEditable;
            if (event.key === "/" && !typing && !document.querySelector(".modal.show")) {
                event.preventDefault();
                input.focus();
                input.select();
            }
        });
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
