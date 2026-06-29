<script>
    const anggotaOptionsUrl = '{{ route('dashboard.anggota-options') }}'
    const anggotaOptionCache = {}
    let anggotaSearchTimer = null

    function showDashboardLoading() {
        const loading = document.getElementById('dashboard-loading')
        if (loading) {
            loading.classList.remove('hidden')
            loading.classList.add('flex')
        }
    }

    function hideDashboardLoading() {
        const loading = document.getElementById('dashboard-loading')
        if (loading) {
            loading.classList.add('hidden')
            loading.classList.remove('flex')
        }
    }

    function syncAnggotaSelection(input) {
        const hidden = input.parentElement.querySelector('[data-anggota-id]')
        const options = anggotaOptionCache[input.dataset.anggotaList] || []
        const selected = options.find(item => item.label === input.value)

        if (hidden) hidden.value = selected ? selected.id : ''
    }

    function loadAnggotaOptions(input) {
        const list = document.getElementById(input.dataset.anggotaList)
        if (!list) return

        input.classList.add('opacity-70')
        const params = new URLSearchParams()
        const keyword = input.value.split(' (')[0]
        if (keyword) params.set('q', keyword)
        if (input.dataset.anggotaTipe) params.set('tipe', input.dataset.anggotaTipe)

        fetch(`${anggotaOptionsUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        })
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(items => {
                anggotaOptionCache[input.dataset.anggotaList] = items
                list.innerHTML = ''

                items.forEach(item => {
                    const option = document.createElement('option')
                    option.value = item.label
                    list.appendChild(option)
                })

                syncAnggotaSelection(input)
            })
            .catch(() => {})
            .finally(() => {
                input.classList.remove('opacity-70')
            })
    }

    document.querySelectorAll('[data-anggota-search]').forEach(input => {
        input.addEventListener('focus', () => loadAnggotaOptions(input))
        input.addEventListener('input', () => {
            syncAnggotaSelection(input)
            clearTimeout(anggotaSearchTimer)
            anggotaSearchTimer = setTimeout(() => loadAnggotaOptions(input), 250)
        })
        input.addEventListener('change', () => syncAnggotaSelection(input))
    })

    document.querySelectorAll('[data-loading-link]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.defaultPrevented || link.classList.contains('pointer-events-none')) return
            if (link.target && link.target !== '_self') return
            showDashboardLoading()
            if (link.dataset.downloadLink !== undefined) {
                window.setTimeout(hideDashboardLoading, 2500)
            }
        })
    })

    document.querySelectorAll('[data-loading-form]').forEach(form => {
        form.addEventListener('submit', () => {
            showDashboardLoading()
            form.querySelectorAll('button[type="submit"]').forEach(button => {
                button.disabled = true
                button.classList.add('opacity-75')
            })
        })
    })
</script>
