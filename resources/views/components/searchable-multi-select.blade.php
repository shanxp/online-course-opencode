@props([
    'name',
    'options' => collect(),
    'placeholder' => __('messages.select_placeholder'),
    'displayField' => 'name',
    'subTextField' => null,
    'selected' => [],
    'disabledIds' => [],
    'autosubmit' => false,
])

@php
    $optionList = collect($options)->map(fn($o) => [
        'id' => $o->id,
        'display' => $o->{$displayField} . ($subTextField && $o->{$subTextField} ? ' (' . $o->{$subTextField} . ')' : ''),
        'search' => $o->{$displayField} . ' ' . ($subTextField && $o->{$subTextField} ? $o->{$subTextField} : ''),
    ])->values()->toArray();
    $selectedIds = collect($selected)->pluck('id')->toArray();
    $disabledIdsArr = collect($disabledIds)->toArray();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        options: {{ json_encode($optionList) }},
        selected: {{ json_encode($selectedIds) }},
        disabledIds: {{ json_encode($disabledIdsArr) }},
        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx >= 0) { this.selected.splice(idx, 1); }
            else if (!this.disabledIds.includes(id)) { this.selected.push(id); }
            this.$refs.hiddenInputs.value = this.selected.join(',');
            @if($autosubmit)
                this.$el.closest('form')?.submit();
            @endif
        },
        isSelected(id) { return this.selected.includes(id); },
        isDisabled(id) { return this.disabledIds.includes(id); },
        remove(id) { this.toggle(id); },
        get filtered() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => o.search.toLowerCase().includes(q));
        },
    }"
    @click.away="open = false"
    class="relative"
>
    <input x-ref="hiddenInputs" type="hidden" name="{{ $name }}" value="{{ implode(',', $selectedIds) }}">

    <div @click="open = !open"
         class="flex items-center gap-1 flex-wrap min-h-[38px] px-3 py-1.5 border border-gray-300 rounded-md bg-white cursor-text">

        <span class="inline-flex items-center gap-1 flex-wrap">
            @foreach($optionList as $opt)
                <span x-show="selected.includes({{ $opt['id'] }})" x-cloak
                      class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-50 border border-primary-200 rounded text-xs text-primary-700">
                    <span class="max-w-[120px] truncate">{{ $opt['display'] }}</span>
                    <button type="button" @click.stop="remove({{ $opt['id'] }})" class="text-primary-400 hover:text-primary-600 shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            @endforeach
        </span>

        <input type="text" x-model="search" @focus="open = true"
               @keydown.escape="open = false"
               placeholder="{{ $placeholder }}"
               class="flex-1 min-w-[100px] border-0 p-0 text-base focus:ring-0 focus:outline-none bg-transparent">
    </div>

    <div x-show="open" x-cloak
         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
        @foreach($optionList as $opt)
            <div x-show='!search || @json($opt["search"]).toLowerCase().includes(search.toLowerCase())'
                 x-cloak
                 @click="toggle({{ $opt['id'] }})"
                 @keydown.enter="toggle({{ $opt['id'] }})"
                 tabindex="0"
                 class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm hover:bg-primary-50 focus:bg-primary-50 focus:outline-none border-b border-gray-50 last:border-b-0"
                 :class="{
                     'bg-primary-50': isSelected({{ $opt['id'] }}) && !isDisabled({{ $opt['id'] }}),
                     'opacity-50 cursor-not-allowed': isDisabled({{ $opt['id'] }}),
                     'cursor-pointer': !isDisabled({{ $opt['id'] }})
                 }"
                 role="option"
                 :aria-selected="isSelected({{ $opt['id'] }}) ? 'true' : 'false'">
                <svg class="w-4 h-4 shrink-0" :class="isSelected({{ $opt['id'] }}) ? 'text-primary-600' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="min-w-0">{{ $opt['display'] }}</span>
                <span x-show="isDisabled({{ $opt['id'] }})" x-cloak class="ml-auto text-xs text-gray-400 shrink-0">{{ __('messages.already_added') }}</span>
            </div>
        @endforeach
        <div x-show="options.length > 0 && filtered.length === 0"
             x-cloak class="px-3 py-2 text-sm text-gray-400">{{ __('messages.no_results') }}</div>
    </div>
</div>
