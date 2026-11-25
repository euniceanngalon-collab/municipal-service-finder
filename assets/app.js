// Requirements checklist: remember ticks per service in this browser only.
(function () {
  var list = document.querySelector('.checklist');
  if (!list) return;
  var key = 'msf:' + list.getAttribute('data-key');
  var boxes = list.querySelectorAll('input[type=checkbox]');
  var out = document.getElementById('req-progress');
  var saved = [];
  try { saved = JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) { saved = []; }

  function update() {
    var done = 0;
    boxes.forEach(function (b) { if (b.checked) done++; });
    if (out) out.textContent = done + ' of ' + boxes.length + ' ready.';
    try {
      var ticked = [];
      boxes.forEach(function (b, i) { if (b.checked) ticked.push(i); });
      localStorage.setItem(key, JSON.stringify(ticked));
    } catch (e) { /* storage unavailable: still works, just not remembered */ }
  }
  boxes.forEach(function (b, i) {
    b.checked = saved.indexOf(i) !== -1;
    b.addEventListener('change', update);
  });
  update();
})();

// Confirm before deleting in admin.
document.addEventListener('submit', function (ev) {
  var f = ev.target;
  if (f.dataset && f.dataset.confirm && !window.confirm(f.dataset.confirm)) ev.preventDefault();
});
