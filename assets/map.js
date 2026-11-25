// Indoor map: shows an animated arrow route from the main entrance to the chosen office.
(function () {
  var NS = 'http://www.w3.org/2000/svg';
  var dataEl = document.getElementById('route-data');
  var select = document.getElementById('plan-office');
  if (!dataEl || !select) return;

  var routes = JSON.parse(dataEl.textContent || '{}');
  var floorNames = JSON.parse(document.getElementById('floor-names').textContent || '{}');
  var tabs = document.querySelectorAll('.plan-tab');
  var floors = document.querySelectorAll('.plan-floor');
  var result = document.getElementById('plan-result');
  var stepsEl = document.getElementById('plan-steps');
  var nextBtn = document.getElementById('plan-next');
  var current = null;

  function showFloor(key) {
    tabs.forEach(function (t) {
      var on = t.getAttribute('data-floor') === key;
      t.classList.toggle('on', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    floors.forEach(function (f) { f.classList.toggle('on', f.getAttribute('data-floor') === key); });
  }

  function el(name, attrs) {
    var n = document.createElementNS(NS, name);
    for (var k in attrs) n.setAttribute(k, attrs[k]);
    return n;
  }

  function clearRoute() {
    document.querySelectorAll('.route-layer').forEach(function (g) { while (g.firstChild) g.removeChild(g.firstChild); });
    document.querySelectorAll('.plan-room.target').forEach(function (r) { r.classList.remove('target'); });
    tabs.forEach(function (t) { t.classList.remove('has-route'); });
  }

  function drawSegment(layer, pts, isLast) {
    var d = pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0] + ' ' + p[1]; }).join(' ');
    var path = el('path', { d: d, 'class': 'route-line' });
    layer.appendChild(path);
    var total = path.getTotalLength();
    // chevron arrows along the path, pointing in the walking direction
    var gap = 38, i = 0;
    for (var pos = 26; pos < total - 10; pos += gap, i++) {
      var a = path.getPointAtLength(pos), b = path.getPointAtLength(Math.min(total, pos + 2));
      var ang = Math.atan2(b.y - a.y, b.x - a.x) * 180 / Math.PI;
      var arrow = el('polygon', {
        points: '-8,-7 8,0 -8,7 -3,0',
        'class': 'route-arrow',
        transform: 'translate(' + a.x + ' ' + a.y + ') rotate(' + ang + ')'
      });
      arrow.style.animationDelay = (i * 0.14) + 's';
      layer.appendChild(arrow);
    }
    // destination pin on the last segment
    if (isLast) {
      var end = pts[pts.length - 1];
      layer.appendChild(el('circle', { cx: end[0], cy: end[1], r: 9, 'class': 'route-end' }));
      layer.appendChild(el('circle', { cx: end[0], cy: end[1], r: 16, 'class': 'route-end-pulse' }));
    }
  }

  function showRoute(id) {
    clearRoute();
    var r = routes[id];
    current = r || null;
    if (!r) { result.hidden = true; return; }

    var order = ['ground', 'second'].filter(function (f) { return r.segments[f]; });
    order.forEach(function (f, idx) {
      var layer = document.querySelector('.route-layer[data-floor="' + f + '"]');
      if (layer) drawSegment(layer, r.segments[f], idx === order.length - 1);
      var tab = document.querySelector('.plan-tab[data-floor="' + f + '"]');
      if (tab) tab.classList.add('has-route');
    });
    var room = document.querySelector('.plan-room[data-spot="' + r.spot + '"]');
    if (room) room.classList.add('target');

    showFloor(order[0]);

    document.getElementById('plan-title').textContent = r.name;
    document.getElementById('plan-status').textContent = r.open;
    document.getElementById('plan-hours').textContent = r.hours ? '· ' + r.hours : '';
    document.getElementById('plan-note').textContent = r.note || '';
    document.getElementById('plan-office-link').href = (window.MSF_OFFICE_URL || 'office.php?id=') + encodeURIComponent(id);
    stepsEl.innerHTML = '';
    r.steps.forEach(function (s) {
      var li = document.createElement('li');
      li.textContent = s;
      stepsEl.appendChild(li);
    });

    if (order.length > 1) {
      nextBtn.hidden = false;
      nextBtn.setAttribute('data-to', order[1]);
      nextBtn.textContent = 'Then see the ' + (floorNames[order[1]] || 'next floor') + ' →';
    } else {
      nextBtn.hidden = true;
    }
    result.hidden = false;
  }

  select.addEventListener('change', function () {
    showRoute(select.value);
    try {
      var u = new URL(window.location.href);
      if (select.value) u.searchParams.set('office', select.value); else u.searchParams.delete('office');
      history.replaceState(null, '', u.toString());
    } catch (e) { /* older browser: map still works */ }
  });
  tabs.forEach(function (t) {
    t.addEventListener('click', function () { showFloor(t.getAttribute('data-floor')); });
  });
  nextBtn.addEventListener('click', function () { showFloor(nextBtn.getAttribute('data-to')); });

  if (select.value) showRoute(select.value);
})();