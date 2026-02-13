<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\B2bCatalogRequest;
use Botble\Marketplace\Models\B2bCatalog;

class B2bCatalogForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(B2bCatalog::class)
            ->setValidatorClass(B2bCatalogRequest::class)
            ->add('title', TextField::class, NameFieldOption::make()->label(__('Title'))->required())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->label(__('Description')))
            ->add('pdf_file', 'file', [
                'label' => __('PDF File'),
                'attr' => [
                    'accept' => '.pdf',
                    'id' => 'pdf_file',
                ],
                'help_block' => [
                    'text' => $this->getModel()?->pdf_path
                        ? __('Current file: :file. Upload a new file to replace it.', ['file' => basename($this->getModel()->pdf_path)])
                        : __('Upload a PDF file.'),
                ],
            ])
            ->add('upload_progress', HtmlField::class, HtmlFieldOption::make()->content($this->getUploadProgressHtml()));
    }

    protected function getUploadProgressHtml(): string
    {
        $indexRoute = route('marketplace.b2b-catalogs.index');

        return <<<HTML
<div id="upload-progress-toast" style="display:none; position:fixed; bottom:30px; right:30px; z-index:9999; min-width:320px;">
    <div class="card shadow-lg border-0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong id="upload-status-text">Uploading PDF...</strong>
                <span id="upload-percent-text" class="text-primary fw-bold">0%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <small id="upload-size-text" class="text-muted mt-1 d-block"></small>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form');
    var mainForm = null;
    forms.forEach(function(f) {
        if (f.querySelector('#pdf_file')) mainForm = f;
    });
    if (!mainForm) return;

    mainForm.addEventListener('submit', function (e) {
        var fileInput = document.getElementById('pdf_file');
        if (!fileInput || !fileInput.files.length) return;

        e.preventDefault();

        var toast = document.getElementById('upload-progress-toast');
        var bar = document.getElementById('upload-progress-bar');
        var percentText = document.getElementById('upload-percent-text');
        var statusText = document.getElementById('upload-status-text');
        var sizeText = document.getElementById('upload-size-text');
        var submitBtn = mainForm.querySelector('button[type="submit"]');

        toast.style.display = 'block';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
        }

        var formData = new FormData(mainForm);
        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) {
                var pct = Math.round((ev.loaded / ev.total) * 100);
                bar.style.width = pct + '%';
                percentText.textContent = pct + '%';
                sizeText.textContent = formatBytes(ev.loaded) + ' / ' + formatBytes(ev.total);
                if (pct >= 100) {
                    statusText.textContent = 'Processing...';
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                }
            }
        });

        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                statusText.textContent = 'Upload Complete!';
                bar.classList.add('bg-success');
                percentText.textContent = '100%';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.next_url) { window.location.href = resp.next_url; return; }
                } catch (ex) {}
                window.location.href = '{$indexRoute}';
            } else {
                statusText.textContent = 'Upload Failed!';
                bar.classList.add('bg-danger');
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = 'Retry'; }
                try {
                    var err = JSON.parse(xhr.responseText);
                    if (err.message) sizeText.textContent = err.message;
                } catch (ex) { sizeText.textContent = 'Server error.'; }
            }
        });

        xhr.addEventListener('error', function () {
            statusText.textContent = 'Upload Failed!';
            bar.classList.add('bg-danger');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = 'Retry'; }
            sizeText.textContent = 'Network error.';
        });

        xhr.open(mainForm.method, mainForm.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    });

    function formatBytes(b) {
        if (b === 0) return '0 B';
        var k = 1024, s = ['B', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(b) / Math.log(k));
        return parseFloat((b / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
    }
});
</script>
HTML;
    }
}
