@php
    $productId = request()->route('id');
    $unitsUrl = backpack_url('product/'.$productId.'/units');
@endphp

<section class="product-unit-panel" data-product-unit-panel data-product-id="{{ $productId }}"
    data-list-url="{{ $unitsUrl }}" data-store-url="{{ $unitsUrl }}">
    <div class="product-unit-panel__heading">
        <div>
            <h3>Inventory units</h3>
            <p data-unit-count>Loading units...</p>
        </div>
        <button type="button" class="btn btn-primary" data-open-unit-dialog>
            <i class="la la-plus" aria-hidden="true"></i> Add unit
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th scope="col">SKU</th>
                    <th scope="col">Original website</th>
                    <th scope="col">Storage / Color</th>
                    <th scope="col">Condition</th>
                    <th scope="col">Selling price</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody data-unit-rows>
                <tr><td colspan="6" class="text-muted">Loading units...</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="product-unit-dialog" data-unit-dialog aria-labelledby="product-unit-dialog-title">
        <div class="product-unit-dialog__header">
            <h3 id="product-unit-dialog-title">Add unit</h3>
            <button type="button" class="btn btn-sm btn-light" data-close-unit-dialog aria-label="Close">&times;</button>
        </div>
        <div class="product-unit-dialog__body">
            <div class="product-unit-form-grid">
                <label>SKU <input data-unit-field="sku" data-required="SKU" aria-required="true" maxlength="100"></label>
                <label>Serial number <input data-unit-field="serial_number" maxlength="100"></label>
                <label class="product-unit-form-grid__wide">Original website
                    <input data-unit-field="website" type="text" inputmode="url" maxlength="2048" placeholder="https://...">
                </label>
                <label>Storage <input data-unit-field="storage" maxlength="100" placeholder="e.g. 128 GB"></label>
                <label>Color <input data-unit-field="color" maxlength="100"></label>
                <label>Battery health (%) <input data-unit-field="battery_health" type="text" inputmode="numeric"></label>
                <label>Condition
                    <select data-unit-field="condition" data-required="Condition" aria-required="true">
                        @foreach (\App\Models\ProductUnit::CONDITIONS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Selling price (NGN)
                    <input data-unit-field="selling_price" data-required="Selling price" inputmode="decimal" aria-required="true" placeholder="50,000">
                </label>
                <label>Status
                    <select data-unit-field="status" data-required="Status" aria-required="true">
                        @foreach (\App\Models\ProductUnit::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="product-unit-form-grid__wide">Defects
                    <textarea data-unit-field="defects" rows="2"></textarea>
                </label>
                <label class="product-unit-form-grid__wide">Included accessories
                    <textarea data-unit-field="included_accessories" rows="2"></textarea>
                </label>
                <label class="product-unit-form-grid__wide">Photos
                    <input data-unit-field="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                </label>
            </div>
            <p class="product-unit-dialog__error" data-unit-error role="alert" aria-live="polite" hidden></p>
        </div>
        <div class="product-unit-dialog__footer">
            <button type="button" class="btn btn-light" data-close-unit-dialog>Cancel</button>
            <button type="button" class="btn btn-primary" data-save-unit>Save unit</button>
        </div>
    </dialog>
</section>

<style>
    .product-unit-panel__heading,
    .product-unit-dialog__header,
    .product-unit-dialog__footer {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
    }
    .product-unit-panel__heading { margin-bottom: 1rem; }
    .product-unit-panel__heading h3,
    .product-unit-dialog__header h3 { font-size: 1rem; margin: 0; }
    .product-unit-panel__heading p { color: var(--tblr-secondary, #667085); margin: .25rem 0 0; }
    .product-unit-dialog {
        border: 0;
        border-radius: .5rem;
        box-shadow: 0 1rem 3rem rgb(0 0 0 / 20%);
        max-height: calc(100vh - 2rem);
        max-width: 760px;
        padding: 0;
        width: calc(100vw - 2rem);
    }
    .product-unit-dialog::backdrop { background: rgb(16 24 40 / 45%); }
    .product-unit-dialog__header,
    .product-unit-dialog__footer { padding: 1rem 1.25rem; }
    .product-unit-dialog__header { border-bottom: 1px solid var(--tblr-border-color, #dee2e6); }
    .product-unit-dialog__body { max-height: calc(100vh - 12rem); overflow: auto; padding: 1.25rem; }
    .product-unit-dialog__footer { border-top: 1px solid var(--tblr-border-color, #dee2e6); }
    .product-unit-form-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .product-unit-form-grid label { display: grid; font-weight: 600; gap: .35rem; }
    .product-unit-form-grid input,
    .product-unit-form-grid select,
    .product-unit-form-grid textarea {
        border: 1px solid var(--tblr-border-color, #ced4da);
        border-radius: .25rem;
        font: inherit;
        font-weight: 400;
        min-height: 2.4rem;
        padding: .45rem .65rem;
        width: 100%;
    }
    .product-unit-form-grid textarea { min-height: auto; }
    .product-unit-form-grid__wide { grid-column: 1 / -1; }
    .product-unit-dialog__error { color: var(--tblr-danger, #d63939); margin: 1rem 0 0; }
    @media (max-width: 600px) {
        .product-unit-form-grid { grid-template-columns: 1fr; }
        .product-unit-form-grid__wide { grid-column: auto; }
    }
</style>

<script>
    (() => {
        const initializeUnitPanel = () => {
            const panel = document.querySelector('[data-product-unit-panel]');
            if (!panel || panel.dataset.initialized) return;
            panel.dataset.initialized = 'true';

            const rows = panel.querySelector('[data-unit-rows]');
            const count = panel.querySelector('[data-unit-count]');
            const dialog = panel.querySelector('[data-unit-dialog]');
            const error = panel.querySelector('[data-unit-error]');
            const saveButton = panel.querySelector('[data-save-unit]');
            let units = [];

            const renderUnits = () => {
                rows.replaceChildren();
                count.textContent = `${units.length} ${units.length === 1 ? 'unit' : 'units'}`;

                if (!units.length) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 6;
                    cell.className = 'text-muted';
                    cell.textContent = 'No units added yet.';
                    row.append(cell);
                    rows.append(row);
                    return;
                }

                units.forEach((unit) => {
                    const row = document.createElement('tr');
                    const values = [
                        unit.sku,
                        [unit.storage, unit.color].filter(Boolean).join(' / ') || '-',
                        unit.condition,
                        unit.selling_price,
                        unit.status,
                    ];
                    values.forEach((value) => {
                        const cell = document.createElement('td');
                        cell.textContent = value || '-';
                        row.append(cell);
                    });
                    const websiteCell = document.createElement('td');
                    if (unit.website) {
                        const link = document.createElement('a');
                        link.href = unit.website;
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                        link.textContent = 'Visit source';
                        websiteCell.append(link);
                    } else {
                        websiteCell.textContent = '-';
                    }
                    row.insertBefore(websiteCell, row.children[1]);
                    rows.append(row);
                });
            };

            const loadUnits = async () => {
                try {
                    const response = await fetch(panel.dataset.listUrl, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    });
                    if (!response.ok) throw new Error('Could not load product units.');
                    units = (await response.json()).units;
                    renderUnits();
                } catch (loadError) {
                    count.textContent = 'Units unavailable';
                    rows.replaceChildren();
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 6;
                    cell.className = 'text-danger';
                    cell.textContent = loadError.message;
                    row.append(cell);
                    rows.append(row);
                }
            };

            panel.querySelector('[data-open-unit-dialog]').addEventListener('click', () => {
                error.hidden = true;
                error.textContent = '';
                dialog.showModal();
                panel.querySelector('[data-unit-field="sku"]').focus();
            });
            panel.querySelectorAll('[data-close-unit-dialog]').forEach((button) => {
                button.addEventListener('click', () => dialog.close());
            });

            saveButton.addEventListener('click', async () => {
                error.hidden = true;
                error.textContent = '';

                const missingField = Array.from(panel.querySelectorAll('[data-required]'))
                    .find((field) => !field.value.trim());
                if (missingField) {
                    error.textContent = `${missingField.dataset.required} is required.`;
                    error.hidden = false;
                    missingField.focus();
                    return;
                }

                const data = new FormData();
                data.append('product_id', panel.dataset.productId);
                panel.querySelectorAll('[data-unit-field]').forEach((field) => {
                    if (field.type === 'file') {
                        Array.from(field.files).forEach((file) => data.append('photos[]', file));
                    } else {
                        data.append(field.dataset.unitField, field.value);
                    }
                });

                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
                try {
                    const response = await fetch(panel.dataset.storeUrl, {
                        method: 'POST',
                        body: data,
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        credentials: 'same-origin',
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const messages = Object.values(result.errors || {}).flat();
                        throw new Error(messages.join(' ') || result.message || 'Could not save this unit.');
                    }

                    units.unshift(result.unit);
                    renderUnits();
                    panel.querySelectorAll('[data-unit-field]').forEach((field) => {
                        if (field.dataset.unitField === 'condition') field.value = 'excellent';
                        else if (field.dataset.unitField === 'status') field.value = 'available';
                        else field.value = '';
                    });
                    dialog.close();
                } catch (saveError) {
                    error.textContent = saveError.message;
                    error.hidden = false;
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = 'Save unit';
                }
            });

            loadUnits();

            if (new URLSearchParams(window.location.search).get('open_units') === '1') {
                const unitsTab = Array.from(document.querySelectorAll('.nav-link, [role="tab"]'))
                    .find((tab) => tab.textContent.trim() === 'Units');
                unitsTab?.click();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeUnitPanel, { once: true });
        } else {
            initializeUnitPanel();
        }
    })();
</script>