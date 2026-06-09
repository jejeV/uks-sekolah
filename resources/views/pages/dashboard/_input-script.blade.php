<script>
    const anggotaOptionsUrl = '{{ route('dashboard.anggota-options') }}'
    const anggotaOptionCache = {}
    let anggotaSearchTimer = null

    function syncAnggotaSelection(input) {
        const hidden = input.parentElement.querySelector('[data-anggota-id]')
        const options = anggotaOptionCache[input.dataset.anggotaList] || []
        const selected = options.find(item => item.label === input.value)

        if (hidden) hidden.value = selected ? selected.id : ''
    }

    function loadAnggotaOptions(input) {
        const list = document.getElementById(input.dataset.anggotaList)
        if (!list) return

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
</script>
