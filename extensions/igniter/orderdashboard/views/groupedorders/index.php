<div class="row-fluid" style="margin-top: -15px;">
    <h4 class="pl-2 pt-0 mt-0">Grouped Orders by Time Slot</h4>
</div>
<div style="display: none;" class="print-display-area" id="print-display-area"></div>

<!-- All Orders -->

<div class="row-fluid">    
    <div class="control-statuseditor"
        data-control="status-editor"
        data-alias="formStatusId"
        >
        <?php echo $this->renderList(); ?>
    </div>
</div>

<div class="modal slideInDown fade"
        id="previewModalGrouped"
        tabindex="-1"
        role="dialog"
        aria-labelledby="previewModalTitle"
        aria-hidden="true"
>
    <div class="modal-dialog" role="document" style="min-width: 800px;">
        <div id="previewModalContentGrouped" class="modal-content">
            <div class="modal-body">
                <div class="progress-indicator">
                    <span class="spinner"><span class="ti-loading fa-3x fa-fw"></span></span>
                    <?= e(lang('admin::lang.text_loading')) ?>
                </div>
            </div>
        </div>
    </div>
</div>
