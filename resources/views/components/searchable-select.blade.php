@props([
    'name',
    'options' => collect(),
    'placeholder' => __('messages.select_placeholder'),
    'displayField' => 'name',
    'subTextField' => null,
    'selected' => null,
    'required' => false,
    'autosubmit' => false,
])

@php
    $jsonOptions = collect($options)->map(fn($o) => [
        'id' => $o->id,
        'display' => $o->{$displayField} . ($subTextField && $o->{$subTextField} ? ' (' . $o->{$subTextField} . ')' : ''),
        'search' => $o->{$displayField} . ' ' . ($subTextField && $o->{$subTextField} ? $o->{$subTextField} : ''),
    ])->values()->toArray();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selectedId: {{ $selected ?: 'null' }},
        selectedLabel: '',
        options: {{ json_encode($jsonOptions) }},
        get filtered() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => o.search.toLowerCase().includes(q));
        },
        select(opt) {
            this.selectedId = opt.id;
            this.selectedLabel = opt.display;
            this.search = '';
            this.open = false;
            @if($autosubmit)
                this.$el.closest('form')?.submit();
            @endif
        },
        clear() {
            this.selectedId = null;
            this.selectedLabel = '';
            this.search = '';
            @if($autosubmit)
                this.$el.closest('form')?.submit();
            @endif
        },
        init() {
            if (this.selectedId) {
                const opt = this.options.find(o => o.id === this.selectedId);
                if (opt) this.selectedLabel = opt.display;
            }
        }
    }"
    @click.away="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId ?? ''">

    <template x-if="!selectedId">
        <div class="relative">
            <input type="text" x-model="search" @focus="open = true" @keydown.escape="open = false"
                   @keydown.down.prevent="$nextTick(() => $el.parentElement.nextElementSibling?.querySelector('[data-result]')?.focus())"
                   placeholder="{{ $placeholder }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </template>

    <template x-if="selectedId">
        <div class="flex items-center gap-2 px-3 py-1.5 bg-primary-50 border border-primary-200 rounded-md text-sm text-primary-700">
            <span class="flex-1 truncate" x-text="selectedLabel"></span>
            <button type="button" @click="clear()" class="shrink-0 text-primary-400 hover:text-primary-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>

    <div x-show="open && filtered.length" x-cloak
         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
        <template x-for="opt in filtered" :key="opt.id">
            <button type="button" data-result
                    @click="select(opt)" @keydown.enter="select(opt)"
                    @keydown.up.prevent="$el.previousElementSibling?.focus()"
                    @keydown.down.prevent="$el.nextElementSibling?.focus()"
                    class="block w-full text-left px-3 py-2 text-sm hover:bg-primary-50 focus:bg-primary-50 focus:outline-none border-b border-gray-50 last:border-b-0"
                    x-text="opt.display"></button>
        </template>
    </div>

    <div x-show="open && filtered.length === 0" x-cloak
         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg">
        <div class="px-3 py-2 text-sm text-gray-400">{{ __('messages.no_results') }}</div>
    </div>
</div>
