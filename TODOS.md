# Testing Checklist - Filament Compatibility

## Actions Integration
- [ ] ExportAction in table header actions
- [ ] ExportBulkAction in table bulk actions
- [ ] ExportAction on resource pages
- [ ] Actions display correct icons (heroicon-o-arrow-down-tray)
- [ ] Actions respect Filament authorization policies
- [ ] Multiple export templates with `exports()` method
- [ ] Form interactions work correctly in actions

## Column Source Methods
- [ ] `fromTable()` - extracts columns from table definition
- [ ] `fromForm()` - extracts fields from form definition
- [ ] `fromModel()` - uses model attributes with form headings fallback
- [ ] Column caching works correctly (`$cachedMap`)
- [ ] Toggleable columns are respected in table exports
- [ ] Hidden toggleable columns are excluded from export

## Table Column Integration
- [ ] Table column state resolution via `getStateFromRecord()`
- [ ] Table column transformations are applied
- [ ] Table column separators are handled correctly
- [ ] Custom `getStateUsing()` closures work
- [ ] Custom `formatStateUsing()` closures work
- [ ] Table column actions are properly reset before queue serialization
- [ ] Column headings match table column labels

## Form Field Integration
- [ ] Form fields extract correctly including nested fields
- [ ] Relationship fields in forms (`belongsTo()`, `hasMany()`, etc.)
- [ ] Nested relationship paths (e.g., `user.company.name`)
- [ ] Repeater fields are properly skipped
- [ ] Builder fields are properly skipped
- [ ] Field labels become column headings

## Relationship Support
- [ ] BelongsTo relationships export correctly
- [ ] HasMany relationships export correctly
- [ ] Relationship data from table columns
- [ ] Nested relationships with dot notation

## Data Mapping & Formatting
- [ ] Boolean false values show as 0 (not blank)
- [ ] Boolean true values show as 1
- [ ] Array values formatted to comma-separated strings (ArrayFormatter)
- [ ] Enum values formatted to strings (EnumFormatter)
- [ ] Object values formatted to strings (ObjectFormatter)
- [ ] Null values handled correctly
- [ ] Model hidden attributes respected by default
- [ ] `only()` method filters columns correctly
- [ ] `except()` method filters columns correctly
- [ ] `except([])` override works to export hidden fields

## Query Handling
- [ ] Table query with filters and sorting (`fromTable()`)
- [ ] Custom query modifications via `modifyQueryUsing()`
- [ ] Record IDs filtering (bulk exports)
- [ ] RelationManager queries work correctly
- [ ] String vs integer key types handled correctly

## Queue Support
- [ ] Exports can be queued with `queue()`
- [ ] Chunk size configuration works
- [ ] Query serialization with EloquentSerialize
- [ ] Livewire component references cleared before queueing
- [ ] Closures evaluated to static values before queueing
- [ ] Queue notifications display correctly
- [ ] ExportFinishedEvent dispatched correctly
- [ ] Temporary files stored in correct disk
- [ ] Download via signed routes works
- [ ] RelationManager owner record preserved in queue

## File & Format Options
- [ ] Custom filename configuration
- [ ] Filename form interaction (AskForFilename)
- [ ] Writer type selection (xlsx, csv, etc.)
- [ ] Writer type form interaction (AskForWriterType)
- [ ] Column width configuration
- [ ] Column format configuration (dates, numbers, etc.)
- [ ] Auto-sizing columns works
- [ ] RTL support for right-to-left languages

## Headings
- [ ] Headings generated from table columns
- [ ] Headings generated from form fields
- [ ] Custom headings via closures
- [ ] Headings respect Filament translations

## Multi-Sheet Support
- [ ] Multiple sheets export correctly
- [ ] Sheet titles work correctly

## Resource Integration
- [ ] Resource class detection from Livewire component
- [ ] Resource model detection
- [ ] Resource authorization
- [ ] Page vs RelationManager detection

## Livewire Integration
- [ ] Livewire component hydration
- [ ] Livewire properties accessible in closures
- [ ] Form data passed correctly
- [ ] Table interactions work during export
- [ ] RelationManager owner record handling

## File Management
- [ ] Temporary files created in `storage/app/filament-excel/`
- [ ] Files auto-deleted after download
- [ ] Prune command removes old exports (24 hours)
- [ ] UUID prefixes prevent filename collisions

## Error Handling
- [ ] Invalid column names handled gracefully
- [ ] Missing relationships fail gracefully
- [ ] Queue failures reported correctly
- [ ] Large exports don't timeout

## Closure Evaluation Context
- [ ] `$livewire` parameter available in closures
- [ ] `$resource` parameter available in closures
- [ ] `$model` parameter available in closures
- [ ] `$record` parameter available in closures
- [ ] `$state` parameter available in closures
- [ ] `$column` parameter available in closures

## Serialization (Queue)
- [ ] Table column closures removed for serialization
- [ ] Column state serialized correctly
- [ ] SerializableClosure works for custom closures
- [ ] Model instance serialization works
- [ ] Query builder serialization works
