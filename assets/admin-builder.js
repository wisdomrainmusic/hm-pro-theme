(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	ready(function () {
		var wrap = document.querySelector('.hmpro-builder-wrap');
		if (!wrap) return;

		var layoutField = document.getElementById('hmpro_builder_layout');
		var area = wrap.getAttribute('data-area') || 'header';
		var canvasList = document.getElementById('hmpro-builder-canvas-list');
		var emptyEl = wrap.querySelector('.hmpro-builder-empty');
		var editingKeyEl = wrap.querySelector('.hmpro-builder-editing-key');

		function getLayout() {
			try { return JSON.parse(layoutField.value || '{}'); } catch(e) { return {}; }
		}
		function setLayout(obj) {
			layoutField.value = JSON.stringify(obj || {});
		}

		var sectionBtns = wrap.querySelectorAll('.hmpro-builder-section-btn');
		var elementBtns = wrap.querySelectorAll('.hmpro-builder-element-btn');
		var addFirst = wrap.querySelector('.hmpro-builder-add-first');

		function toast(msg) {
			// Lightweight placeholder feedback.
			if (window.console && console.log) console.log('[HMPro Builder]', msg);
		}

		function ensureSchema(layout) {
			layout = layout && typeof layout === 'object' ? layout : {};
			if (!layout.schema_version) layout.schema_version = 1;
			if (!layout.regions || typeof layout.regions !== 'object') layout.regions = {};

			var regionKeys = (area === 'footer')
				? ['footer_top','footer_main','footer_bottom']
				: ['header_top','header_main','header_bottom'];

			regionKeys.forEach(function (k) {
				if (!Array.isArray(layout.regions[k])) layout.regions[k] = [];
			});

			return layout;
		}

		function regionKeyFromSection(section) {
			section = section || 'top';
			if (section !== 'main' && section !== 'bottom') section = 'top';
			return (area === 'footer' ? 'footer_' : 'header_') + section;
		}

		function uid(prefix) {
			prefix = prefix || 'c';
			return prefix + '_' + Math.random().toString(36).slice(2, 8) + '_' + Date.now().toString(36).slice(2, 6);
		}

		// Commit 019: Single-row/single-col per section by default
		function ensureSingleRowColumn(layout, regionKey) {
			layout = ensureSchema(layout);
			var rows = layout.regions[regionKey];
			if (!rows.length) {
				rows.push({
					id: uid('row'),
					columns: [{
						id: uid('col'),
						width: 12,
						components: []
					}]
				});
			}
			// Ensure first row/col shape exists
			if (!rows[0].columns || !Array.isArray(rows[0].columns) || !rows[0].columns.length) {
				rows[0].columns = [{ id: uid('col'), width: 12, components: [] }];
			}
			if (!rows[0].columns[0].components || !Array.isArray(rows[0].columns[0].components)) {
				rows[0].columns[0].components = [];
			}
			return layout;
		}

		var currentSection = 'top';
		var currentRegionKey = regionKeyFromSection(currentSection);

		function setCurrentSection(section) {
			currentSection = section;
			currentRegionKey = regionKeyFromSection(section);
			if (editingKeyEl) editingKeyEl.textContent = currentRegionKey;
			renderCanvas();
		}

		function getComponentsForCurrentSection(layout) {
			layout = ensureSchema(layout);
			var rows = layout.regions[currentRegionKey] || [];
			if (!rows.length || !rows[0].columns || !rows[0].columns.length) return [];
			return rows[0].columns[0].components || [];
		}

		function setComponentsForCurrentSection(layout, comps) {
			layout = ensureSingleRowColumn(layout, currentRegionKey);
			layout.regions[currentRegionKey][0].columns[0].components = comps;
			return layout;
		}

		function humanLabel(type) {
			switch (type) {
				case 'logo': return 'Logo';
				case 'menu': return 'Menu';
				case 'search': return 'Search';
				case 'cart': return 'Cart';
				case 'button': return 'Button';
				case 'html': return 'HTML';
				case 'spacer': return 'Spacer';
				default: return type;
			}
		}

		function addComponent(type) {
			type = type || 'menu';
			var layout = ensureSchema(getLayout());
			layout = ensureSingleRowColumn(layout, currentRegionKey);

			var comps = getComponentsForCurrentSection(layout);
			comps.push({
				id: uid(type),
				type: type,
				settings: {}
			});

			layout = setComponentsForCurrentSection(layout, comps);
			setLayout(layout);
			renderCanvas();
		}

		function removeComponentById(id) {
			var layout = ensureSchema(getLayout());
			var comps = getComponentsForCurrentSection(layout);
			comps = comps.filter(function (c) { return c && c.id !== id; });
			layout = setComponentsForCurrentSection(layout, comps);
			setLayout(layout);
			renderCanvas();
		}

		function moveComponent(id, dir) {
			var layout = ensureSchema(getLayout());
			var comps = getComponentsForCurrentSection(layout);
			var idx = comps.findIndex(function (c) { return c && c.id === id; });
			if (idx < 0) return;
			var to = idx + (dir === 'up' ? -1 : 1);
			if (to < 0 || to >= comps.length) return;
			var tmp = comps[idx];
			comps[idx] = comps[to];
			comps[to] = tmp;
			layout = setComponentsForCurrentSection(layout, comps);
			setLayout(layout);
			renderCanvas();
		}

		// Drag & drop reorder (lightweight)
		var dragId = null;
		function onDragStart(e) {
			var item = e.currentTarget;
			dragId = item.getAttribute('data-id');
			item.classList.add('is-dragging');
			try { e.dataTransfer.setData('text/plain', dragId); } catch (err) {}
		}
		function onDragEnd(e) {
			var item = e.currentTarget;
			item.classList.remove('is-dragging');
			dragId = null;
		}
		function onDragOver(e) {
			e.preventDefault();
		}
		function onDrop(e) {
			e.preventDefault();
			var target = e.currentTarget;
			var targetId = target.getAttribute('data-id');
			if (!dragId || !targetId || dragId === targetId) return;

			var layout = ensureSchema(getLayout());
			var comps = getComponentsForCurrentSection(layout);
			var from = comps.findIndex(function (c) { return c && c.id === dragId; });
			var to = comps.findIndex(function (c) { return c && c.id === targetId; });
			if (from < 0 || to < 0) return;

			var moved = comps.splice(from, 1)[0];
			comps.splice(to, 0, moved);
			layout = setComponentsForCurrentSection(layout, comps);
			setLayout(layout);
			renderCanvas();
		}

		function renderCanvas() {
			if (!canvasList) return;
			var layout = ensureSchema(getLayout());
			layout = ensureSingleRowColumn(layout, currentRegionKey);
			setLayout(layout); // keep schema normalized

			var comps = getComponentsForCurrentSection(layout);
			canvasList.innerHTML = '';

			if (emptyEl) emptyEl.hidden = comps.length > 0;

			comps.forEach(function (c) {
				if (!c || !c.id) return;

				var item = document.createElement('div');
				item.className = 'hmpro-canvas-item';
				item.setAttribute('data-id', c.id);
				item.setAttribute('draggable', 'true');

				var left = document.createElement('div');
				left.className = 'hmpro-canvas-left';

				var badge = document.createElement('span');
				badge.className = 'hmpro-canvas-badge';
				badge.textContent = humanLabel(c.type);

				var text = document.createElement('div');
				text.className = 'hmpro-canvas-text';

				var name = document.createElement('div');
				name.className = 'hmpro-canvas-name';
				name.textContent = humanLabel(c.type);

				var meta = document.createElement('div');
				meta.className = 'hmpro-canvas-id';
				meta.textContent = c.id;

				text.appendChild(name);
				text.appendChild(meta);

				left.appendChild(badge);
				left.appendChild(text);

				var actions = document.createElement('div');
				actions.className = 'hmpro-canvas-actions';

				var up = document.createElement('button');
				up.type = 'button';
				up.className = 'button';
				up.textContent = '↑';
				up.addEventListener('click', function () { moveComponent(c.id, 'up'); });

				var down = document.createElement('button');
				down.type = 'button';
				down.className = 'button';
				down.textContent = '↓';
				down.addEventListener('click', function () { moveComponent(c.id, 'down'); });

				var remove = document.createElement('button');
				remove.type = 'button';
				remove.className = 'button';
				remove.textContent = 'Remove';
				remove.addEventListener('click', function () { removeComponentById(c.id); });

				actions.appendChild(up);
				actions.appendChild(down);
				actions.appendChild(remove);

				item.appendChild(left);
				item.appendChild(actions);

				item.addEventListener('dragstart', onDragStart);
				item.addEventListener('dragend', onDragEnd);
				item.addEventListener('dragover', onDragOver);
				item.addEventListener('drop', onDrop);

				canvasList.appendChild(item);
			});
		}

		// Section selection
		sectionBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				sectionBtns.forEach(function (b) { b.classList.remove('button-primary'); });
				btn.classList.add('button-primary');
				setCurrentSection(btn.getAttribute('data-section'));
				toast('Section selected: ' + btn.getAttribute('data-section'));
			});
		});

		elementBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var type = btn.getAttribute('data-type');
				addComponent(type);
				toast('Added: ' + type + ' to ' + currentRegionKey);
			});
		});

		if (addFirst) {
			addFirst.addEventListener('click', function () {
				addComponent('menu');
				toast('Added: menu to ' + currentRegionKey);
			});
		}

		// Ensure hidden layout field always has a valid base schema.
		var current = ensureSchema(getLayout());
		setLayout(current);

		// Default select "Top" section on load
		var firstBtn = wrap.querySelector('.hmpro-builder-section-btn[data-section="top"]');
		if (firstBtn) {
			firstBtn.classList.add('button-primary');
		}
		setCurrentSection('top');
	});
})();
