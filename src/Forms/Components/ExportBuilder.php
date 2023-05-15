<?php

namespace pxlrbt\FilamentExcel\Forms\Components;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Hidden;

class ExportBuilder extends Builder
{
    protected string $view = 'filament-excel::components.export-builder';

    protected array|null $columnLabels = null;

    public function getUnusedColumns(): array
    {
        $usedBlocks = collect($this->getState())->pluck('type');
        return collect($this->getBlocks())->filter(fn ($block) => $usedBlocks->doesntContain($block->getName()))->toArray();
    }

    public function getColumnLabels(): array|null
    {
        $this->setColumnLabels();

        return $this->columnLabels;
    }

    protected function setColumnLabels(): void
    {
        if ($this->columnLabels == null) {
            $this->columnLabels = [];
            $blocks = $this->getChildComponents();
            foreach ($blocks as $block) {
                foreach ($block->getChildComponents() as $component) {
                    $this->columnLabels[$component->getName()] = [
                        'name' => $component->getLabel(),
                        'display' => ($component->isHidden() || ($component instanceof Hidden)) ? false : true,
                    ];
                }
            }
        }
    }

    public function childComponents(array | \Closure $components): static
    {
        foreach ($components as $component) {
            foreach ($component->getChildComponents() as $blockComponent) {
                $blockComponent->disableLabel();
            }
            $this->childComponents[] = $component;
        }

        return $this;
    }
}
