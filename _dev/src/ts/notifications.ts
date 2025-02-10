document.addEventListener('DOMContentLoaded', function() {
  const dialog = document.getElementById('dialog-update-notification');

  if (dialog instanceof HTMLDialogElement) {
    setTimeout(() => {
      dialog.showModal();
    }, 1500);
  }
  
  const closeButton = dialog?.querySelector('[data-dismiss="dialog"]');

  if (closeButton) {
    closeButton.addEventListener('click', () => {
      if (dialog instanceof HTMLDialogElement) {
        dialog.close();
      }
    });
  }
});
