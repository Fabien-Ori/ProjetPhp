// Plain Stones - minimal JS (optional enhancements)
document.addEventListener('DOMContentLoaded', function () {
  // Confirm before leaving with unsaved form (optional)
  var forms = document.querySelectorAll('form[data-confirm-unsaved]');
  forms.forEach(function (form) {
    var changed = false;
    form.querySelectorAll('input, textarea').forEach(function (el) {
      el.addEventListener('change', function () { changed = true; });
    });
    window.addEventListener('beforeunload', function (e) {
      if (changed) e.preventDefault();
    });
  });
});
