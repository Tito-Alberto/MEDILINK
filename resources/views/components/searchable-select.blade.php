@props([
    'name',
    'options' => [],
    'selected' => '',
    'placeholder' => 'Buscar',
    'label' => null,
    'allLabel' => 'Todas categorias',
    'id' => null,
])

@php
    $dropdownId = $id ?? 'dropdown-' . uniqid();
    $inputId = $dropdownId . '-input';
    $toggleId = $dropdownId . '-toggle';
    $listId = $dropdownId . '-list';
    $items = $options instanceof Illuminate\Support\Collection ? $options->values() : $options;
@endphp

@if ($label)
    <label class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $label }}</label>
@endif
<div class="relative w-full sm:w-64" id="{{ $dropdownId }}">
    <input
        class="w-full rounded-full border border-slate-300 bg-white/80 px-4 py-2 pr-9 text-sm text-slate-900 placeholder:text-slate-500 focus:border-lime-400 focus:outline-none"
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="text"
        value="{{ $selected }}"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
    />
    <button class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-slate-500 hover:text-lime-700" type="button" id="{{ $toggleId }}" aria-label="Abrir categorias">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6"></path>
        </svg>
    </button>
    <div id="{{ $listId }}" class="absolute z-10 mt-2 hidden max-h-64 w-full overflow-auto overscroll-contain rounded-2xl border border-slate-200 bg-white shadow-xl">
        <button class="block w-full px-4 py-2 text-left text-sm text-slate-600 hover:bg-white" type="button" data-value="">
            {{ $allLabel }}
        </button>
        @foreach ($items as $item)
            <button class="block w-full px-4 py-2 text-left text-sm text-slate-600 hover:bg-white" type="button" data-value="{{ $item }}">
                {{ $item }}
            </button>
        @endforeach
    </div>
</div>
<script>
    (() => {
        const dropdown = document.getElementById('{{ $dropdownId }}');
        const input = document.getElementById('{{ $inputId }}');
        const toggle = document.getElementById('{{ $toggleId }}');
        const list = document.getElementById('{{ $listId }}');

        if (!dropdown || !input || !toggle || !list) {
            return;
        }

        let activeIndex = -1;

        const options = () => Array.from(list.querySelectorAll('[data-value]')).filter((option) => !option.classList.contains('hidden'));

        const openList = () => {
            list.classList.remove('hidden');
            setActiveIndex(0);
        };

        const closeList = () => {
            list.classList.add('hidden');
            clearActive();
        };

        const clearActive = () => {
            list.querySelectorAll('[data-value]').forEach((option) => {
                option.classList.remove('bg-lime-500/10', 'text-lime-700');
            });
            activeIndex = -1;
        };

        const setActiveIndex = (index) => {
            const visibleOptions = options();
            if (!visibleOptions.length) {
                clearActive();
                return;
            }
            const safeIndex = Math.max(0, Math.min(index, visibleOptions.length - 1));
            clearActive();
            visibleOptions[safeIndex].classList.add('bg-lime-500/10', 'text-lime-700');
            activeIndex = safeIndex;
        };

        const filterOptions = () => {
            const term = input.value.trim().toLowerCase();
            list.querySelectorAll('[data-value]').forEach((option) => {
                const value = option.dataset.value.toLowerCase();
                const match = value.includes(term) || option.textContent.toLowerCase().includes(term);
                option.classList.toggle('hidden', !match);
            });
            setActiveIndex(0);
        };

        const applyValue = (value, label) => {
            input.value = value;
            if (!value && label) {
                input.value = '';
            }
            closeList();
        };

        toggle.addEventListener('click', () => {
            if (list.classList.contains('hidden')) {
                filterOptions();
                openList();
            } else {
                closeList();
            }
        });

        input.addEventListener('focus', () => {
            filterOptions();
            openList();
        });

        input.addEventListener('input', () => {
            filterOptions();
            openList();
        });

        input.addEventListener('keydown', (event) => {
            const visibleOptions = options();
            if (!visibleOptions.length) {
                return;
            }
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    setActiveIndex(activeIndex + 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    setActiveIndex(activeIndex - 1);
                    break;
                case 'Enter':
                    if (!list.classList.contains('hidden')) {
                        event.preventDefault();
                        const option = visibleOptions[activeIndex] ?? visibleOptions[0];
                        if (option) {
                            applyValue(option.dataset.value, option.textContent.trim());
                        }
                    }
                    break;
                case 'Escape':
                    closeList();
                    break;
                default:
                    break;
            }
        });

        list.addEventListener('click', (event) => {
            const option = event.target.closest('[data-value]');
            if (!option) {
                return;
            }
            applyValue(option.dataset.value, option.textContent.trim());
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                closeList();
            }
        });
    })();
</script>









