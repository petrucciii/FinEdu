<?php
//mantiene aperto il dizionario interessato dall'ultima operazione
$activeDictionary = session()->getFlashdata('dictionary');
if (!isset($dictionaries[$activeDictionary])) {
    $activeDictionary = array_key_first($dictionaries);
}
?>

<div id="content-wrapper">
    <div class="top-bar d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 fw-bold text-dark">
            <i class="fas fa-list text-primary me-2"></i>Dizionari
        </h4>
    </div>

    <div class="main-content pt-0">
        <div class="accordion shadow-sm rounded-4 overflow-hidden" id="dictionaryAccordion">
            <?php foreach ($dictionaries as $key => $dictionary): ?>
                <?php
                $rows = $items[$key] ?? [];
                $collapseId = 'dictionary-' . $key;
                $isOpen = $key === $activeDictionary;
                $visibleFieldCount = count(array_filter($dictionary['fields'], static function ($field) use ($dictionary) {
                    return $field['name'] !== $dictionary['pk'];
                }));
                ?>
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-white text-dark fw-bold <?= $isOpen ? '' : 'collapsed' ?>"
                            type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId) ?>">
                            <span class="d-flex align-items-center justify-content-between w-100 me-3 gap-3">
                                <span><?= esc($dictionary['title']) ?></span>
                                <span class="badge bg-primary rounded-pill flex-shrink-0"><?= count($rows) ?> voci</span>
                            </span>
                        </button>
                    </h2>

                    <div id="<?= esc($collapseId) ?>"
                        class="accordion-collapse collapse <?= $isOpen ? 'show' : '' ?>"
                        data-bs-parent="#dictionaryAccordion">
                        <div class="accordion-body bg-light p-4">
                            <form action="<?= base_url('admin/DictionaryManagementController/create/' . rawurlencode($key)) ?>"
                                method="post" class="row g-2 align-items-end mb-4 p-3 bg-white rounded-3 border">
                                <?php foreach ($dictionary['fields'] as $field): ?>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold mb-1"><?= esc($field['label']) ?></label>
                                        <input type="<?= ($field['type'] ?? 'text') === 'int' ? 'number' : 'text' ?>"
                                            name="<?= esc($field['name'], 'attr') ?>"
                                            class="form-control form-control-sm"
                                            maxlength="<?= esc((string) ($field['maxlength'] ?? ''), 'attr') ?>"
                                            required>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-md-auto">
                                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                        <i class="fas fa-plus me-1"></i>Aggiungi
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive bg-white rounded-3 border">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <?php foreach ($dictionary['fields'] as $field): ?>
                                                <?php if ($field['name'] !== $dictionary['pk']): ?>
                                                    <th><?= esc($field['label']) ?></th>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <th class="text-end">Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($rows)): ?>
                                            <tr>
                                                <td colspan="<?= $visibleFieldCount + 2 ?>"
                                                    class="text-center text-muted py-3">
                                                    Nessuna voce presente.
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php foreach ($rows as $row): ?>
                                            <?php $formId = 'dict-form-' . $key . '-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $row[$dictionary['pk']]); ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark"><?= esc($row[$dictionary['pk']]) ?></span>
                                                </td>
                                                <?php foreach ($dictionary['fields'] as $field): ?>
                                                    <?php if ($field['name'] === $dictionary['pk']) {
                                                        continue;
                                                    } ?>
                                                    <td>
                                                        <input type="<?= ($field['type'] ?? 'text') === 'int' ? 'number' : 'text' ?>"
                                                            name="<?= esc($field['name'], 'attr') ?>"
                                                            class="form-control form-control-sm"
                                                            value="<?= esc($row[$field['name']] ?? '', 'attr') ?>"
                                                            maxlength="<?= esc((string) ($field['maxlength'] ?? ''), 'attr') ?>"
                                                            form="<?= esc($formId) ?>" required>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="text-end text-nowrap">
                                                    <form id="<?= esc($formId) ?>"
                                                        action="<?= base_url('admin/DictionaryManagementController/update/' . rawurlencode($key)) ?>"
                                                        method="post" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= esc($row[$dictionary['pk']], 'attr') ?>">
                                                    </form>
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light text-primary border shadow-sm me-1"
                                                        form="<?= esc($formId) ?>" title="Salva">
                                                        <i class="fas fa-save"></i>
                                                    </button>

                                                    <form action="<?= base_url('admin/DictionaryManagementController/delete/' . rawurlencode($key)) ?>"
                                                        method="post" class="d-inline"
                                                        onsubmit="return confirm('Disattivare questa voce?');">
                                                        <input type="hidden" name="id" value="<?= esc($row[$dictionary['pk']], 'attr') ?>">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-light text-danger border shadow-sm"
                                                            title="Elimina">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
