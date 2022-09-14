<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    @php
        $containers = $getChildComponentContainers();

        $isCollapsible = $isCollapsible();
        $isItemCreationDisabled = $isItemCreationDisabled();
        $isItemDeletionDisabled = $isItemDeletionDisabled();
        $isItemMovementDisabled = $isItemMovementDisabled();
        $columnLabels = $getColumnLabels();
    @endphp
    <div
        {{-- x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }"  --}}
        x-data="{ isCollapsed: @js($isCollapsed()) }"
        x-on:builder-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
        x-on:builder-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"

        @class([
            "bg-white border border-gray-300 shadow-sm rounded-xl px-4 py-2 relative",
            "dark:bg-gray-800 dark:border-gray-600"  => config('forms.dark_mode'),
        ])
    >
    <div>
        @if ((count($containers) > 1) && $isCollapsible)
            <div class="space-x-2 rtl:space-x-reverse" x-data="{}">
                <x-forms::link
                    x-on:click="$dispatch('builder-collapse', '{{ $getStatePath() }}')"
                    tag="button"
                    size="sm"
                >
                    {{ __('forms::components.builder.buttons.collapse_all.label') }}
                </x-forms::link>

                <x-forms::link
                    x-on:click="$dispatch('builder-expand', '{{ $getStatePath() }}')"
                    tag="button"
                    size="sm"
                >
                    {{ __('forms::components.builder.buttons.expand_all.label') }}
                </x-forms::link>
            </div>
        @endif
    </div>

    <div {{ $attributes->merge($getExtraAttributes())->class([
        'filament-forms-builder-compone nt space-y-6 rounded-xl',
        'bg-gray-50 p-6' => $isInset(),
        'dark:bg-gray-500/10' => $isInset() && config('forms.dark_mode'),
    ]) }}>
        @if (count($containers))
            <table class="w-full text-left rtl:text-right table-auto mx-4 filament-table-repeater" x-show="! isCollapsed">
                <thead>
                <tr>
                    <th class="filament-table-repeater-header-cell">{{ __('Coluna') }}</th>
                    @foreach($columnLabels as $columnLabel)
                        @if($columnLabel['display'])
                            <th class="p-2 filament-table-repeater-header-cell">
                                <span>
                                    {{ $columnLabel['name'] }}
                                </span>
                            </th>
                        @else
                            <th style="display: none"></th>
                        @endif
                    @endforeach
                    @if ((! $isItemMovementDisabled) || $hasBlockLabels || (! $isItemDeletionDisabled) || $isCollapsible)
                        <th class="p-2 filament-table-repeater-header-cell w-20"></th>
                    @endif

                </tr>
                </thead>
                <tbody
                    wire:sortable
                    wire:end.stop="dispatchFormEvent('builder::moveItems', '{{ $getStatePath() }}', $event.target.sortable.toArray())"
                >
                @php
                    $hasBlockLabels = $hasBlockLabels();
                    $hasBlockNumbers = $hasBlockNumbers();
                @endphp

                @foreach ($containers as $uuid => $item)
                    @php
                        $components = collect($item->getComponents())
                            ->mapWithKeys(function ($component) {
                                return [$component->getName() => $component];
                            });
                    @endphp
                    <tr
                        x-data="{
                            isCreateButtonVisible: false,
                            isCollapsed: @js($isCollapsed()),
                        }"
                        x-on:builder-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
                        x-on:builder-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"
                        x-on:click="isCreateButtonVisible = true"
                        x-on:mouseenter="isCreateButtonVisible = true"
                        x-on:click.away="isCreateButtonVisible = false"
                        x-on:mouseleave="isCreateButtonVisible = false"
                        wire:key="{{ $this->id }}.{{ $item->getStatePath() }}.item"
                        wire:sortable.item="{{ $uuid }}"
                    >
                        <td>
                            @php
                                $block = $item->getParentComponent();

                                $block->labelState($item->getRawState());
                            @endphp

                            {{ $item->getParentComponent()->getLabel() }}

                            @php
                                $block->labelState(null);
                            @endphp

                            @if ($hasBlockNumbers)
                                <small class="font-mono">{{ $loop->iteration }}</small>
                            @endif
                        </td>

                        @foreach(array_keys($columnLabels) as $column)
                            <td>
                                {{ $components->get($column) }}
                            </td>
                        @endforeach

                        @if ((! $isItemMovementDisabled) || $hasBlockLabels || (! $isItemDeletionDisabled) || $isCollapsible)
                            <td>
                                <div class="flex justify-end">
                                @unless ($isItemMovementDisabled)
                                    <button
                                        wire:sortable.handle
                                        wire:keydown.prevent.arrow-up="dispatchFormEvent('builder::moveItemUp', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                        wire:keydown.prevent.arrow-down="dispatchFormEvent('builder::moveItemDown', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                        type="button"
                                        @class([
                                            'flex items-center justify-center flex-none w-10 h-10 text-gray-400 border-r rtl:border-l rtl:border-r-0 transition hover:text-gray-300',
                                            'dark:text-gray-400 dark:border-gray-700 dark:hover:text-gray-500' => config('forms.dark_mode'),
                                        ])
                                    >
                                        <span class="sr-only">
                                            {{ __('forms::components.builder.buttons.move_item_down.label') }}
                                        </span>

                                        <x-heroicon-s-switch-vertical class="w-4 h-4"/>
                                    </button>
                                @endunless

                                <ul @class([
                                    'flex divide-x rtl:divide-x-reverse',
                                    'dark:divide-gray-700' => config('forms.dark_mode'),
                                ])>
                                    @unless ($isItemDeletionDisabled)
                                        <li>
                                            <button
                                                wire:click="dispatchFormEvent('builder::deleteItem', '{{ $getStatePath() }}', '{{ $uuid }}')"
                                                type="button"
                                                @class([
                                                    'flex items-center justify-center flex-none w-10 h-10 text-danger-600 transition hover:text-danger-500',
                                                    'dark:text-danger-500 dark:hover:text-danger-400' => config('forms.dark_mode'),
                                                ])
                                            >
                                                <span class="sr-only">
                                                    {{ __('forms::components.builder.buttons.delete_item.label') }}
                                                </span>

                                                <x-heroicon-s-trash class="w-4 h-4"/>
                                            </button>
                                        </li>
                                    @endunless

                                    @if ($isCollapsible)
                                        <li>
                                            <button
                                                x-on:click="isCollapsed = !isCollapsed"
                                                type="button"
                                                @class([
                                                    'flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-300',
                                                    'dark:text-gray-400 dark:hover:text-gray-500' => config('forms.dark_mode'),
                                                ])
                                            >
                                                <x-heroicon-s-minus-sm class="w-4 h-4" x-show="! isCollapsed"/>

                                                <span class="sr-only" x-show="! isCollapsed">
                                                    {{ __('forms::components.builder.buttons.collapse_item.label') }}
                                                </span>

                                                <x-heroicon-s-plus-sm class="w-4 h-4" x-show="isCollapsed" x-cloak/>

                                                <span class="sr-only" x-show="isCollapsed" x-cloak>
                                                    {{ __('forms::components.builder.buttons.expand_item.label') }}
                                                </span>
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            </table>
        @endif

        @if (! $isItemCreationDisabled)
            <x-forms::dropdown class="flex justify-center">
                <x-slot name="trigger">
                    <x-forms::button size="sm">
                        {{ $getCreateItemButtonLabel() }}
                    </x-forms::button>
                </x-slot>

                <x-forms::builder.block-picker
                    :blocks="$getUnusedColumns()"
                    :state-path="$getStatePath()"
                />
            </x-forms::dropdown>
        @endif
    </div>
    </div>
</x-dynamic-component>
