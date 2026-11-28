document.addEventListener('DOMContentLoaded', () => {
  if (!window.Jodit) return;

  document.querySelectorAll('[data-jodit-editor]').forEach((textarea) => {
    const editor = window.Jodit.make(textarea, {
      language: 'vi',
      height: 260,
      minHeight: 180,
      toolbarAdaptive: true,
      askBeforePasteHTML: false,
      askBeforePasteFromWord: false,
      showCharsCounter: true,
      showWordsCounter: true,
      showXPathInStatusbar: false,
      uploader: {
        insertImageAsBase64URI: true,
      },
      placeholder: textarea.dataset.joditPlaceholder || '',
      buttons: [
        'source', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
        'ul', 'ol', 'outdent', 'indent', '|', 'font', 'fontsize', 'brush',
        'paragraph', 'align', '|', 'link', 'table', 'hr', '|', 'eraser',
        'undo', 'redo',
      ],
    });

    textarea.closest('form')?.addEventListener('submit', () => {
      editor.updateElement();
    });
  });
});
