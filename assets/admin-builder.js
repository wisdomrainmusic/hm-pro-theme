(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function uid(prefix) {
		return (prefix || 'id') + '_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 7);
	}

	function ensureBaseSchema(area, obj) {
		var a = area === 'footer' ? 'footer' : 'header';
		var regions = a === 'footer'
			? ['footer_top', 'footer_main', 'footer_bottom']
			: ['header_top', 'header_main', 'header_bottom'];

		var out = obj && typeof obj === 'object' ? obj : {};
		if (!out.schema_version) out.schema_version = 1;
		if (!out.regions || typeof out.regions !== 'object') out.regions = {};
		regions.forEach(function (k) {
			if (!Array.isArray(out.regions[k])) out.regions[k] = [];
		});
		return out;
	}

	function regionKeyFromSection(area, section) {
		var a = area === 'footer' ? 'footer' : 'header';
		var s = section || 'main';
		if (s !== 'top' && s !== 'main' && s !== 'bottom') s = 'main';
		return a + '_' + s;
	}

	function defaultSettingsFor(type, area, regionKey) {
		switch (type) {
			case 'menu':
				// Smart default: top region -> topbar, footer -> footer, otherwise primary
				var loc = 'primary';
				if (String(regionKey).indexOf('top') !== -1 && area === 'header') loc = 'topbar';
				if (area === 'footer') loc = 'footer';
				return { source: 'location', location: loc, depth: 2, alignment: 'left' };
			case 'button':
				return { text: 'Button', url: '#', alignment: 'left' };
			case 'html':
				return { content: '<span>Custom HTML</span>' };
			case 'spacer':
				return { width: 16, height: 16 };
			case 'search':
				return { placeholder: 'Search…' };
			default:
				return {};
		}
	}

	ready(function () {
		var wrap = document.querySelector('.hmpro-builder-wrap');
		if (!wrap) return;

		var layoutField = document.getElementById('hmpro_builder_layout');
		var area = wrap.getAttribute('data-area') || 'header';
		area = area === 'footer' ? 'footer' : 'header';

		var sectionBtns = wrap.querySelectorAll('.hmpro-builder-section-btn');
		var elementBtns = wrap.querySelectorAll('.hmpro-builder-element-btn');
		var addFirst = wrap.querySelector('.hmpro-builder-add-first');
		var canvasInner = wrap.querySelector('.hmpro-builder-canvas-inner');
		var emptyState = wrap.querySelector('.hmpro-builder-empty');
		var stage = wrap.querySelector('.hmpro-builder-stage');
		if (!stage && canvasInner) {
			stage = document.createElement('div');
			stage.className = 'hmpro-builder-stage';
			canvasInner.appendChild(stage);
		}

		function toast(msg) {
			if (window.console && console.log) console.log('[HMPro Builder]', msg);
		}

		function getLayout() {
			try {
				return ensureBaseSchema(area, JSON.parse(layoutField.value || '{}'));
			} catch (e) {
				return ensureBaseSchema(area, {});
			}
		}

		function setLayout(obj) {
			layoutField.value = JSON.stringify(ensureBaseSchema(area, obj || {}));
		}

		var currentSection = 'top';
		function setSection(section) {
			currentSection = section;
			sectionBtns.forEach(function (b) { b.classList.remove('button-primary'); });
			var active = wrap.querySelector('.hmpro-builder-section-btn[data-section="' + section + '"]');
			if (active) active.classList.add('button-primary');
			render();
		}

		function ensureRowAndCol(layout, regionKey) {
			if (!layout.regions[regionKey].length) {
				layout.regions[regionKey].push({
					id: uid('row'),
					columns: [
						{
							id: uid('col'),
							width: 12,
							components: []
						}
					]
				});
			}
			var row = layout.regions[regionKey][0];
			if (!row.columns || !row.columns.length) {
				row.columns = [{ id: uid('col'), width: 12, components: [] }];
			}
			if (!row.columns[0].components) row.columns[0].components = [];
			return row.columns[0];
		}

		function addComponent(type) {
			var t = String(type || '').trim();
			if (!t) return;

			var layout = getLayout();
			var regionKey = regionKeyFromSection(area, currentSection);
			var col = ensureRowAndCol(layout, regionKey);

			col.components.push({
				id: uid(t),
				type: t,
				settings: defaultSettingsFor(t, area, regionKey)
			});

			setLayout(layout);
			render();
			toast('Added: ' + t + ' → ' + regionKey);
		}

		function removeComponent(compId) {
			var layout = getLayout();
			var regionKey = regionKeyFromSection(area, currentSection);
			var rows = layout.regions[regionKey] || [];
			rows.forEach(function (row) {
				(row.columns || []).forEach(function (col) {
					col.components = (col.components || []).filter(function (c) {
						return c && c.id !== compId;
					});
				});
			});
			setLayout(layout);
			render();
		}

		function render() {
			if (!stage) return;
			var layout = getLayout();
			var regionKey = regionKeyFromSection(area, currentSection);
			var rows = layout.regions[regionKey] || [];

			// Build flat list of components for now (first row/first col focus).
			var comps = [];
			rows.forEach(function (row) {
				(row.columns || []).forEach(function (col) {
					(col.components || []).forEach(function (c) {
						if (c && c.type) comps.push(c);
					});
				});
			});

			stage.innerHTML = '';

			if (!comps.length) {
				if (emptyState) emptyState.style.display = '';
				stage.style.display = 'none';
				return;
			}

			if (emptyState) emptyState.style.display = 'none';
			stage.style.display = '';

			var title = document.createElement('div');
			title.className = 'hmpro-builder-stage-title';
			title.textContent = 'Editing: ' + regionKey;
			stage.appendChild(title);

			var list = document.createElement('div');
			list.className = 'hmpro-builder-comp-list';
			stage.appendChild(list);

			comps.forEach(function (c) {
				var card = document.createElement('div');
				card.className = 'hmpro-builder-comp-card';

				var label = document.createElement('div');
				label.className = 'hmpro-builder-comp-label';
				label.textContent = c.type;

				var meta = document.createElement('div');
				meta.className = 'hmpro-builder-comp-meta';
				meta.textContent = c.id;

				var actions = document.createElement('div');
				actions.className = 'hmpro-builder-comp-actions';

				var del = document.createElement('button');
				del.type = 'button';
				del.className = 'button hmpro-builder-comp-remove';
				del.textContent = 'Remove';
				del.addEventListener('click', function () {
					removeComponent(c.id);
				});

				actions.appendChild(del);
				card.appendChild(label);
				card.appendChild(meta);
				card.appendChild(actions);
				list.appendChild(card);
			});
		}

		// Wire section buttons
		sectionBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				setSection(btn.getAttribute('data-section'));
			});
		});

		// Wire element buttons
		elementBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				addComponent(btn.getAttribute('data-type'));
			});
		});

		// Add-first: add a Menu by default
		if (addFirst) {
			addFirst.addEventListener('click', function () {
				addComponent('menu');
			});
		}

		// Init base schema + default section selection
		setLayout(getLayout());
		setSection('top');
	});
})();
