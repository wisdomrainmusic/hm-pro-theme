(function () {
	'use strict';

	/**
	 * Backward compatibility:
	 * NOTE: We are restoring a dedicated Footer Menu widget.
	 * Do NOT normalize footer_menu -> menu anymore.
	 */
	function normalizeCompType(type) {
		var normalized = (type || '').toLowerCase();
		return normalized;
	}

	function normalizeLayoutObject(layoutObj) {
		if (!layoutObj || typeof layoutObj !== 'object' || !layoutObj.regions) return layoutObj;

		Object.keys(layoutObj.regions).forEach(function (sectionKey) {
			var rows = layoutObj.regions[sectionKey];
			if (!Array.isArray(rows)) return;
			rows.forEach(function (row) {
				if (!row || !Array.isArray(row.columns)) return;
				row.columns.forEach(function (column) {
					if (!column || !Array.isArray(column.components)) return;
					column.components.forEach(function (comp) {
						if (!comp || typeof comp !== 'object' || !comp.type) return;
						comp.type = normalizeCompType(comp.type);
					});
				});
			});
		});

		return layoutObj;
	}

	var data = window.hmproBuilderData || {};
	var layoutField = document.getElementById('hmproBuilderLayoutField');
	var sectionButtons = document.querySelectorAll('.hmpro-builder-section-btn');
	var elementButtons = document.querySelectorAll('.hmpro-builder-element-btn');
	var editingLabel = document.querySelector('.hmpro-builder-editing-key');

	var zoneLeft = document.getElementById('hmproZoneLeft');
	var zoneCenter = document.getElementById('hmproZoneCenter');
	var zoneCenterLeft = document.getElementById('hmproZoneCenterLeft');
	var zoneCenterRight = document.getElementById('hmproZoneCenterRight');
	var zoneRight = document.getElementById('hmproZoneRight');

	var modal = document.getElementById('hmproCompModal');
	var modalBody = document.getElementById('hmproModalBody');
	var modalSave = document.getElementById('hmproModalSave');
	var builderForm = layoutField ? layoutField.form : null;
	var isAutoSubmit = false;

	if (!layoutField || !zoneLeft || !zoneRight) return;

	// Zones: default header/footer (3 zones), mega uses 4 zones.
	var ZONES = Array.isArray(data.zones) && data.zones.length ? data.zones.slice() : ['left', 'center', 'right'];

	var layout;
	try {
		layout = JSON.parse(layoutField.value || '{}');
	} catch (e) {
		layout = data.layout || { schema_version: 1, regions: {} };
	}
	layout = normalizeLayoutObject(layout);
	var currentLayout = layout;

	var activeSection = (data.area === 'footer') ? 'footer_top' : ((data.area === 'mega') ? 'mega_content' : 'header_top');
	var activeEditing = null;

	function uid(prefix) {
		return prefix + '_' + Math.random().toString(36).slice(2, 8) + '_' + Date.now().toString(36).slice(2, 6);
	}

	function ensureRegion(sectionKey) {
		layout.schema_version = 1;
		layout.regions = layout.regions || {};
		layout.regions[sectionKey] = layout.regions[sectionKey] || [];
	}

	function ensureSingleRowCols(sectionKey) {
		ensureRegion(sectionKey);
		if (!layout.regions[sectionKey].length) {
			var cols = [];
			var w = (ZONES.length === 4) ? 3 : 4;
			ZONES.forEach(function (z) {
				cols.push({ id: uid('col_' + z), width: w, components: [] });
			});
			layout.regions[sectionKey].push({
				id: uid('row'),
				columns: cols
			});
			return;
		}

		var row = layout.regions[sectionKey][0];
		if (!row || !Array.isArray(row.columns) || row.columns.length === 0) {
			var cols2 = [];
			var w2 = (ZONES.length === 4) ? 3 : 4;
			ZONES.forEach(function (z) {
				cols2.push({ id: uid('col_' + z), width: w2, components: [] });
			});
			layout.regions[sectionKey] = [{
				id: uid('row'),
				columns: cols2
			}];
			return;
		}

		// Normalize columns count to ZONES length.
		while (row.columns.length < ZONES.length) {
			row.columns.push({ id: uid('col_extra'), width: (ZONES.length === 4 ? 3 : 4), components: [] });
		}
		row.columns = row.columns.slice(0, ZONES.length);

		var w3 = (ZONES.length === 4) ? 3 : 4;
		for (var i = 0; i < ZONES.length; i++) {
			row.columns[i].width = w3;
			row.columns[i].components = Array.isArray(row.columns[i].components) ? row.columns[i].components : [];
		}
	}

	function getZoneListEl(zone) {
		if (zone === 'left') return zoneLeft;
		if (zone === 'center') return zoneCenter;
		if (zone === 'center_left') return zoneCenterLeft;
		if (zone === 'center_right') return zoneCenterRight;
		return zoneRight;
	}

	function getComponents(sectionKey, zone) {
		ensureSingleRowCols(sectionKey);
		var row = layout.regions[sectionKey][0];
		var idx = ZONES.indexOf(zone);
		if (idx < 0 || !row || !row.columns || !row.columns[idx]) {
			return [];
		}
		return row.columns[idx].components || [];
	}

	function setComponents(sectionKey, zone, comps) {
		ensureSingleRowCols(sectionKey);
		var row = layout.regions[sectionKey][0];
		var idx = ZONES.indexOf(zone);
		if (idx < 0 || !row || !row.columns || !row.columns[idx]) {
			return;
		}
		row.columns[idx].components = comps;
	}

	function findComponentById(sectionKey, compId) {
		if (!compId) return null;
		ensureSingleRowCols(sectionKey);
		var row = layout.regions[sectionKey][0];
		if (!row || !Array.isArray(row.columns)) return null;
		for (var i = 0; i < row.columns.length; i++) {
			var comps = row.columns[i].components || [];
			for (var j = 0; j < comps.length; j++) {
				if (comps[j] && comps[j].id === compId) {
					return { zone: ZONES[i], index: j, comp: comps[j] };
				}
			}
		}
		return null;
	}

	function syncLayoutToField() {
		var field = document.getElementById('hmpro_builder_layout') || layoutField;
		if (!field) return;
		field.value = JSON.stringify(currentLayout);
	}

	function sync() {
		syncLayoutToField();
	}

	function titleize(type) {
		var t = (type || '').toString();
		return t.charAt(0).toUpperCase() + t.slice(1);
	}

	function render() {
		ensureSingleRowCols(activeSection);

		if (editingLabel) {
			editingLabel.textContent = activeSection;
		}

		ZONES.forEach(function (zone) {
			var listEl = getZoneListEl(zone);
			while (listEl.firstChild) listEl.removeChild(listEl.firstChild);

			var comps = getComponents(activeSection, zone);
			if (!comps.length) {
				var empty = document.createElement('li');
				empty.className = 'hmpro-empty';
				empty.textContent = (data.i18n && data.i18n.empty) ? data.i18n.empty : 'Drop components here.';
				listEl.appendChild(empty);
				return;
			}

			comps.forEach(function (comp, idx) {
				var compType = normalizeCompType(comp.type);
				var li = document.createElement('li');
				li.className = 'hmpro-canvas-item';
				li.draggable = true;
				li.dataset.zone = zone;
				li.dataset.index = String(idx);
				li.dataset.compId = comp.id || '';

				var label = document.createElement('div');
				label.className = 'hmpro-label';

				var pill = document.createElement('span');
				pill.className = 'hmpro-pill';
				pill.textContent = titleize(compType);

				var tw = document.createElement('div');
				tw.className = 'hmpro-titlewrap';
				var strong = document.createElement('strong');
				strong.textContent = titleize(compType);
				var meta = document.createElement('div');
				meta.className = 'hmpro-meta';
				meta.textContent = comp.id || '';
				tw.appendChild(strong);
				tw.appendChild(meta);

				label.appendChild(pill);
				label.appendChild(tw);

				var actions = document.createElement('div');
				actions.className = 'hmpro-actions';

				var up = document.createElement('button');
				up.type = 'button';
				up.className = 'button';
				up.textContent = '↑';
				up.disabled = idx === 0;
				up.addEventListener('click', function () {
					var arr = getComponents(activeSection, zone).slice();
					var t = arr[idx - 1];
					arr[idx - 1] = arr[idx];
					arr[idx] = t;
					setComponents(activeSection, zone, arr);
					sync();
					render();
				});

				var down = document.createElement('button');
				down.type = 'button';
				down.className = 'button';
				down.textContent = '↓';
				down.disabled = idx === comps.length - 1;
				down.addEventListener('click', function () {
					var arr = getComponents(activeSection, zone).slice();
					var t = arr[idx + 1];
					arr[idx + 1] = arr[idx];
					arr[idx] = t;
					setComponents(activeSection, zone, arr);
					sync();
					render();
				});

				var settings = document.createElement('button');
				settings.type = 'button';
				settings.className = 'button';
				settings.textContent = '⚙';
				settings.addEventListener('click', function () { openSettings(zone, idx); });

				var remove = document.createElement('button');
				remove.type = 'button';
				remove.className = 'button';
				remove.textContent = 'Remove';
				remove.addEventListener('click', function () {
					var arr = getComponents(activeSection, zone).slice();
					arr.splice(idx, 1);
					setComponents(activeSection, zone, arr);
					sync();
					render();
				});

				actions.appendChild(up);
				actions.appendChild(down);
				actions.appendChild(settings);
				actions.appendChild(remove);

				li.appendChild(label);
				li.appendChild(actions);

				li.addEventListener('dragstart', function (ev) {
					li.classList.add('is-dragging');
					ev.dataTransfer.effectAllowed = 'move';
					ev.dataTransfer.setData('text/plain', JSON.stringify({
						section: activeSection,
						fromZone: zone,
						fromIndex: idx
					}));
				});
				li.addEventListener('dragend', function () {
					li.classList.remove('is-dragging');
					document.querySelectorAll('.hmpro-zone.is-over').forEach(function (z) {
						z.classList.remove('is-over');
					});
				});

				listEl.appendChild(li);
			});
		});
	}

	function addComponent(type) {
		if (!type) return;
		// Default drop zone:
		// - Header/Footer builders have "center"
		// - Mega builder has 4 zones: left/center_left/center_right/right (no "center")
		var zone = 'center';
		if (ZONES.indexOf(zone) === -1) {
			zone = (ZONES && ZONES.length) ? ZONES[0] : 'left';
		}
		var arr = getComponents(activeSection, zone).slice();
		arr.push({ id: uid(type), type: type, settings: {} });
		setComponents(activeSection, zone, arr);
		sync();
		render();
	}

	sectionButtons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			activeSection = btn.getAttribute('data-section');
			sectionButtons.forEach(function (b) { b.classList.remove('button-primary'); });
			btn.classList.add('button-primary');
			render();
			sync();
		});
	});

	elementButtons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			addComponent(btn.getAttribute('data-type'));
		});
	});

	document.querySelectorAll('.hmpro-zone').forEach(function (zoneWrap) {
		var zone = zoneWrap.getAttribute('data-zone');
		zoneWrap.addEventListener('dragover', function (ev) {
			ev.preventDefault();
			zoneWrap.classList.add('is-over');
			ev.dataTransfer.dropEffect = 'move';
		});

		zoneWrap.addEventListener('dragleave', function () {
			zoneWrap.classList.remove('is-over');
		});

		zoneWrap.addEventListener('drop', function (ev) {
			ev.preventDefault();
			zoneWrap.classList.remove('is-over');
			var payload;
			try {
				payload = JSON.parse(ev.dataTransfer.getData('text/plain') || '{}');
			} catch (e) {
				return;
			}
			if (!payload || payload.section !== activeSection) return;

			var fromZone = payload.fromZone;
			var fromIndex = parseInt(payload.fromIndex, 10);
			if (!fromZone || isNaN(fromIndex)) return;

			var fromArr = getComponents(activeSection, fromZone).slice();
			var moved = fromArr.splice(fromIndex, 1)[0];
			if (!moved) return;

			var toArr = getComponents(activeSection, zone).slice();
			toArr.push(moved);

			setComponents(activeSection, fromZone, fromArr);
			setComponents(activeSection, zone, toArr);

			sync();
			render();
		});
	});

	function openModal() {
		if (!modal) return;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
	}

	function closeModal() {
		if (!modal) return;
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		activeEditing = null;
	}

	// Modal close behavior:
	// - Cancel button (data-modal-cancel="1") closes without saving.
	// - Overlay/X close will auto-save the current settings to avoid user confusion.
	document.querySelectorAll('[data-modal-close="1"]').forEach(function (el) {
		el.addEventListener('click', function () {
			if (!el.hasAttribute('data-modal-cancel') && modalSave && activeEditing) {
				// Triggers the same logic as clicking the Save button.
				modalSave.click();
				return;
			}
			closeModal();
		});
	});

	function openSettings(zone, idx) {
		var comps = getComponents(activeSection, zone);
		var comp = comps[idx];
		if (!comp) return;

		activeEditing = {
			section: activeSection,
			zone: zone,
			index: idx,
			compId: comp.id
		};
		while (modalBody.firstChild) modalBody.removeChild(modalBody.firstChild);

		var type = normalizeCompType(comp.type);
		var settings = comp.settings || {};

		if (type === 'mega_column_menu') {
			var wrap = document.createElement('div');
			wrap.className = 'hmpro-field';

			var lMenu = document.createElement('label');
			lMenu.textContent = 'WP Menu';
			var sMenu = document.createElement('select');
			sMenu.id = 'hmproSettingMegaMenuId';
			var menus = Array.isArray(data.megaMenus) ? data.megaMenus : [];
			if (!menus.length) {
				var o0 = document.createElement('option');
				o0.value = '';
				o0.textContent = '(No menus found)';
				sMenu.appendChild(o0);
			} else {
				menus.forEach(function (m) {
					var o = document.createElement('option');
					o.value = String(m.id);
					o.textContent = m.name;
					sMenu.appendChild(o);
				});
			}
			sMenu.value = settings.menu_id ? String(settings.menu_id) : (menus[0] ? String(menus[0].id) : '');
			wrap.appendChild(lMenu);
			wrap.appendChild(sMenu);
			modalBody.appendChild(wrap);

			var wrapRoot = document.createElement('div');
			wrapRoot.className = 'hmpro-field';
			var lRoot = document.createElement('label');
			lRoot.textContent = 'Root item';
			var sRoot = document.createElement('select');
			sRoot.id = 'hmproSettingMegaRootItem';
			wrapRoot.appendChild(lRoot);
			wrapRoot.appendChild(sRoot);
			modalBody.appendChild(wrapRoot);

			var wrapDepth = document.createElement('div');
			wrapDepth.className = 'hmpro-field';
			var lDepth = document.createElement('label');
			lDepth.textContent = 'Max depth';
			var sDepth = document.createElement('select');
			sDepth.id = 'hmproSettingMegaDepth';
			['1', '2', '3'].forEach(function (v) {
				var o = document.createElement('option');
				o.value = v;
				o.textContent = v;
				sDepth.appendChild(o);
			});
			sDepth.value = settings.max_depth ? String(settings.max_depth) : '2';
			wrapDepth.appendChild(lDepth);
			wrapDepth.appendChild(sDepth);
			modalBody.appendChild(wrapDepth);

			var wrapShow = document.createElement('div');
			wrapShow.className = 'hmpro-field';
			var lShow = document.createElement('label');
			lShow.textContent = 'Show root title';
			var chk = document.createElement('input');
			chk.type = 'checkbox';
			chk.id = 'hmproSettingMegaShowRoot';
			chk.checked = settings.show_root_title ? true : false;
			wrapShow.appendChild(lShow);
			wrapShow.appendChild(chk);
			modalBody.appendChild(wrapShow);

			function loadRootItems(menuId, preselectId) {
				while (sRoot.firstChild) sRoot.removeChild(sRoot.firstChild);
				var optLoading = document.createElement('option');
				optLoading.value = '';
				optLoading.textContent = 'Loading...';
				sRoot.appendChild(optLoading);

				var fd = new FormData();
				fd.append('action', 'hmpro_get_menu_root_items');
				fd.append('menu_id', menuId || '');
				fd.append('_ajax_nonce', data.nonce || '');

				fetch(data.ajaxUrl || ajaxurl, { method: 'POST', credentials: 'same-origin', body: fd })
					.then(function (r) { return r.json(); })
					.then(function (resp) {
						while (sRoot.firstChild) sRoot.removeChild(sRoot.firstChild);
						if (!resp || !resp.success || !Array.isArray(resp.data)) {
							var o = document.createElement('option');
							o.value = '';
							o.textContent = '(No items found)';
							sRoot.appendChild(o);
							return;
						}
						resp.data.forEach(function (it) {
							var o = document.createElement('option');
							o.value = String(it.id);
							o.textContent = it.title;
							sRoot.appendChild(o);
						});
						var val = preselectId ? String(preselectId) : (resp.data[0] ? String(resp.data[0].id) : '');
						sRoot.value = val;
					})
					.catch(function () {
						while (sRoot.firstChild) sRoot.removeChild(sRoot.firstChild);
						var o = document.createElement('option');
						o.value = '';
						o.textContent = '(Error loading items)';
						sRoot.appendChild(o);
					});
			}

			sMenu.addEventListener('change', function () {
				loadRootItems(sMenu.value, '');
			});
			loadRootItems(sMenu.value, settings.root_item_id || '');
		}

		// Footer Info widget (restore settings)
		// Allows storing company/contact text (address/phone/email) as multi-line.
		if (type === 'footer_info' || type === 'footer-info') {
			var fWrap = document.createElement('div');
			fWrap.className = 'hmpro-field';

			var l1 = document.createElement('label');
			l1.textContent = 'Title (optional)';
			var inTitle = document.createElement('input');
			inTitle.type = 'text';
			inTitle.id = 'hmproSettingFooterInfoTitle';
			inTitle.className = 'widefat';
			inTitle.value = (settings.title ? String(settings.title) : '');

			var l2 = document.createElement('label');
			l2.textContent = 'Lines (one per line)';
			var ta = document.createElement('textarea');
			ta.id = 'hmproSettingFooterInfoLines';
			ta.className = 'widefat';
			ta.rows = 6;
			ta.value = (settings.lines ? String(settings.lines) : '');

			var help = document.createElement('p');
			help.style.marginTop = '8px';
			help.style.opacity = '0.8';
			help.textContent = 'Example: Address, Phone, Email. Each line will be printed separately.';

			fWrap.appendChild(l1);
			fWrap.appendChild(inTitle);
			fWrap.appendChild(document.createElement('div')).style.height = '10px';
			fWrap.appendChild(l2);
			fWrap.appendChild(ta);
			fWrap.appendChild(help);

			modalBody.appendChild(fWrap);

			// Saved via the global modalSave handler.
		}

		if (type === 'footer_menu') {
			var footerMenuField = document.createElement('div');
			footerMenuField.className = 'hmpro-field';
			var footerMenuLabel = document.createElement('label');
			footerMenuLabel.textContent = 'Select menu';
			var footerMenuSelect = document.createElement('select');
			footerMenuSelect.id = 'hmproSettingFooterMenuId';
			footerMenuSelect.className = 'widefat';

			var menus = (data.wp_menus && Array.isArray(data.wp_menus)) ? data.wp_menus : [];
			var footerMenuDefault = document.createElement('option');
			footerMenuDefault.value = '';
			footerMenuDefault.textContent = '— Select —';
			footerMenuSelect.appendChild(footerMenuDefault);

			menus.forEach(function (m) {
				var optMenu = document.createElement('option');
				optMenu.value = String(m.id);
				optMenu.textContent = m.name;
				footerMenuSelect.appendChild(optMenu);
			});

			footerMenuSelect.value = settings.menu_id ? String(settings.menu_id) : '';
			footerMenuField.appendChild(footerMenuLabel);
			footerMenuField.appendChild(footerMenuSelect);
			modalBody.appendChild(footerMenuField);

			var footerTitleField = document.createElement('div');
			footerTitleField.className = 'hmpro-field';
			var footerTitleLabel = document.createElement('label');
			footerTitleLabel.textContent = 'Show title';
			footerTitleLabel.style.display = 'flex';
			footerTitleLabel.style.alignItems = 'center';
			footerTitleLabel.style.gap = '8px';
			var footerTitleInput = document.createElement('input');
			footerTitleInput.type = 'checkbox';
			footerTitleInput.id = 'hmproSettingFooterMenuShowTitle';
			footerTitleInput.checked = !!settings.show_title;
			footerTitleLabel.prepend(footerTitleInput);
			footerTitleField.appendChild(footerTitleLabel);
			modalBody.appendChild(footerTitleField);
		} else if (type === 'menu' || type === 'header_menu' || type === 'primary_menu') {
			var field = document.createElement('div');
			field.className = 'hmpro-field';
			var label = document.createElement('label');
			label.textContent = (data.i18n && data.i18n.menuLocation) ? data.i18n.menuLocation : 'Menu location';
			var select = document.createElement('select');
			select.id = 'hmproSettingMenuLocation';

			var locs = data.menuLocations || {};
			var keys = Object.keys(locs);

			if (!keys.length) {
				var opt = document.createElement('option');
				opt.value = 'primary';
				opt.textContent = 'primary';
				select.appendChild(opt);
			} else {
				keys.forEach(function (k) {
					var opt = document.createElement('option');
					opt.value = k;
					opt.textContent = k;
					select.appendChild(opt);
				});
			}

			select.value = settings.location || (keys[0] || 'primary');
			field.appendChild(label);
			field.appendChild(select);
			modalBody.appendChild(field);
		} else if (type === 'search') {
			var sField = document.createElement('div');
			sField.className = 'hmpro-field';
			var sLabel = document.createElement('label');
			sLabel.textContent = 'Placeholder';
			var sInput = document.createElement('input');
			sInput.type = 'text';
			sInput.id = 'hmproSettingSearchPlaceholder';
			sInput.value = settings.placeholder || '';
			sField.appendChild(sLabel);
			sField.appendChild(sInput);
			modalBody.appendChild(sField);
		} else if (type === 'button') {
			var f1 = document.createElement('div');
			f1.className = 'hmpro-field';
			var l1 = document.createElement('label');
			l1.textContent = 'Text';
			var i1 = document.createElement('input');
			i1.type = 'text';
			i1.id = 'hmproSettingButtonText';
			i1.value = settings.text || '';
			f1.appendChild(l1);
			f1.appendChild(i1);

			var f2 = document.createElement('div');
			f2.className = 'hmpro-field';
			var l2 = document.createElement('label');
			l2.textContent = 'URL';
			var i2 = document.createElement('input');
			i2.type = 'url';
			i2.id = 'hmproSettingButtonUrl';
			i2.value = settings.url || '';
			f2.appendChild(l2);
			f2.appendChild(i2);

			modalBody.appendChild(f1);
			modalBody.appendChild(f2);
		} else if (type === 'html') {
			var hField = document.createElement('div');
			hField.className = 'hmpro-field';
			var hLabel = document.createElement('label');
			hLabel.textContent = 'HTML';
			var ta = document.createElement('textarea');
			ta.id = 'hmproSettingHtmlContent';
			ta.rows = 6;
			ta.value = settings.content || '';
			hField.appendChild(hLabel);
			hField.appendChild(ta);
			modalBody.appendChild(hField);
		} else if (type === 'spacer') {
			var sp1 = document.createElement('div');
			sp1.className = 'hmpro-field';
			var spl1 = document.createElement('label');
			spl1.textContent = 'Width (px)';
			var spi1 = document.createElement('input');
			spi1.type = 'text';
			spi1.id = 'hmproSettingSpacerWidth';
			spi1.value = settings.width || '';
			sp1.appendChild(spl1);
			sp1.appendChild(spi1);

			var sp2 = document.createElement('div');
			sp2.className = 'hmpro-field';
			var spl2 = document.createElement('label');
			spl2.textContent = 'Height (px)';
			var spi2 = document.createElement('input');
			spi2.type = 'text';
			spi2.id = 'hmproSettingSpacerHeight';
			spi2.value = settings.height || '';
			sp2.appendChild(spl2);
			sp2.appendChild(spi2);

			modalBody.appendChild(sp1);
			modalBody.appendChild(sp2);
		} else if (type === 'social') {
			var networks = [
				{ key: 'facebook', label: 'Facebook URL' },
				{ key: 'instagram', label: 'Instagram URL' },
				{ key: 'x', label: 'X (Twitter) URL' },
				{ key: 'youtube', label: 'YouTube URL' },
				{ key: 'tiktok', label: 'TikTok URL' },
				{ key: 'linkedin', label: 'LinkedIn URL' },
				{ key: 'whatsapp', label: 'WhatsApp URL' },
				{ key: 'telegram', label: 'Telegram URL' }
			];

			networks.forEach(function(n){
				var f = document.createElement('div');
				f.className = 'hmpro-field';
				var l = document.createElement('label');
				l.textContent = n.label;
				var i = document.createElement('input');
				i.type = 'url';
				i.id = 'hmproSettingSocial_' + n.key;
				i.placeholder = 'https://';
				i.value = (settings.urls && settings.urls[n.key]) ? settings.urls[n.key] : '';
				f.appendChild(l);
				f.appendChild(i);
				modalBody.appendChild(f);
			});

			var fSize = document.createElement('div');
			fSize.className = 'hmpro-field';
			var lSize = document.createElement('label');
			lSize.textContent = 'Size';
			var sSize = document.createElement('select');
			sSize.id = 'hmproSettingSocialSize';
			['small','normal','large'].forEach(function(v){
				var o=document.createElement('option');
				o.value=v;
				o.textContent=v;
				sSize.appendChild(o);
			});
			sSize.value = settings.size || 'normal';
			fSize.appendChild(lSize);
			fSize.appendChild(sSize);
			modalBody.appendChild(fSize);

			var fGap = document.createElement('div');
			fGap.className = 'hmpro-field';
			var lGap = document.createElement('label');
			lGap.textContent = 'Gap';
			var sGap = document.createElement('select');
			sGap.id = 'hmproSettingSocialGap';
			['small','normal','large'].forEach(function(v){
				var o=document.createElement('option');
				o.value=v;
				o.textContent=v;
				sGap.appendChild(o);
			});
			sGap.value = settings.gap || 'normal';
			fGap.appendChild(lGap);
			fGap.appendChild(sGap);
			modalBody.appendChild(fGap);

			var fTab = document.createElement('div');
			fTab.className = 'hmpro-field';
			var lTab = document.createElement('label');
			lTab.textContent = 'Open in new tab';
			var cTab = document.createElement('input');
			cTab.type = 'checkbox';
			cTab.id = 'hmproSettingSocialNewTab';
			cTab.checked = !!settings.new_tab;
			fTab.appendChild(lTab);
			fTab.appendChild(cTab);
			modalBody.appendChild(fTab);

		} else if (type === 'social_icon_button') {
			var urlField = document.createElement('div');
			urlField.className = 'hmpro-field';
			var urlLabel = document.createElement('label');
			urlLabel.textContent = 'URL';
			var urlInput = document.createElement('input');
			urlInput.type = 'url';
			urlInput.id = 'hmproSettingSocialIconUrl';
			urlInput.placeholder = 'https://';
			urlInput.value = settings.url || '';
			urlField.appendChild(urlLabel);
			urlField.appendChild(urlInput);
			modalBody.appendChild(urlField);

			var tabField = document.createElement('div');
			tabField.className = 'hmpro-field';
			var tabLabel = document.createElement('label');
			tabLabel.textContent = 'Open in new tab';
			var tabInput = document.createElement('input');
			tabInput.type = 'checkbox';
			tabInput.id = 'hmproSettingSocialIconNewTab';
			tabInput.checked = !!settings.new_tab;
			tabField.appendChild(tabLabel);
			tabField.appendChild(tabInput);
			modalBody.appendChild(tabField);

			var transparentField = document.createElement('div');
			transparentField.className = 'hmpro-field';
			var transparentLabel = document.createElement('label');
			transparentLabel.textContent = 'Transparent icon style';
			var transparentInput = document.createElement('input');
			transparentInput.type = 'checkbox';
			transparentInput.id = 'hmproSettingSocialIconTransparent';
			transparentInput.checked = !!settings.transparent;
			transparentField.appendChild(transparentLabel);
			transparentField.appendChild(transparentInput);
			modalBody.appendChild(transparentField);

			var modeField = document.createElement('div');
			modeField.className = 'hmpro-field';
			var modeLabel = document.createElement('label');
			modeLabel.textContent = 'Icon mode';
			var modeSelect = document.createElement('select');
			modeSelect.id = 'hmproSettingSocialIconMode';
			['preset', 'custom'].forEach(function (mode) {
				var opt = document.createElement('option');
				opt.value = mode;
				opt.textContent = mode;
				modeSelect.appendChild(opt);
			});
			modeSelect.value = settings.icon_mode || 'preset';
			modeField.appendChild(modeLabel);
			modeField.appendChild(modeSelect);
			modalBody.appendChild(modeField);

			var presetField = document.createElement('div');
			presetField.className = 'hmpro-field';
			var presetLabel = document.createElement('label');
			presetLabel.textContent = 'Preset icon';
			var presetSelect = document.createElement('select');
			presetSelect.id = 'hmproSettingSocialIconPreset';
			['facebook', 'instagram', 'linkedin', 'x', 'youtube', 'tiktok', 'whatsapp', 'telegram'].forEach(function (preset) {
				var optPreset = document.createElement('option');
				optPreset.value = preset;
				optPreset.textContent = preset;
				presetSelect.appendChild(optPreset);
			});
			presetSelect.value = settings.icon_preset || 'facebook';
			presetField.appendChild(presetLabel);
			presetField.appendChild(presetSelect);
			modalBody.appendChild(presetField);

			var customField = document.createElement('div');
			customField.className = 'hmpro-field';
			var customLabel = document.createElement('label');
			customLabel.textContent = 'Custom icon (SVG)';
			var customTextarea = document.createElement('textarea');
			customTextarea.id = 'hmproSettingSocialIconCustom';
			customTextarea.rows = 6;
			customTextarea.value = settings.custom_icon || '';
			customField.appendChild(customLabel);
			customField.appendChild(customTextarea);
			modalBody.appendChild(customField);

			var toggleIconInputs = function () {
				var mode = modeSelect.value || 'preset';
				if (mode === 'custom') {
					customField.style.display = '';
					presetField.style.display = 'none';
				} else {
					customField.style.display = 'none';
					presetField.style.display = '';
				}
			};
			modeSelect.addEventListener('change', toggleIconInputs);
			toggleIconInputs();

		} else {
			var p = document.createElement('p');
			p.textContent = 'No settings for this component yet.';
			modalBody.appendChild(p);
		}

		openModal();
	}

	if (modalSave) {
		modalSave.addEventListener('click', function () {
			if (!activeEditing) return;

			var sectionKey = activeEditing.section || activeSection;

			var match = findComponentById(sectionKey, activeEditing.compId);
			if (!match && sectionKey !== activeSection) {
				match = findComponentById(activeSection, activeEditing.compId);
				sectionKey = activeSection;
			}

			if (!match) {
				closeModal();
				return;
			}

			var zone = match.zone;
			var index = match.index;
			var comps = getComponents(sectionKey, zone);
			if (!comps || !comps[index]) {
				closeModal();
				return;
			}

			var comp = comps[index];
			comp.settings = comp.settings || {};

			var type = normalizeCompType(comp.type);

			if (type === 'mega_column_menu') {
				comp.settings = comp.settings || {};
				comp.settings.source = 'wp_menu';
				var mId = document.getElementById('hmproSettingMegaMenuId');
				var rId = document.getElementById('hmproSettingMegaRootItem');
				var dId = document.getElementById('hmproSettingMegaDepth');
				var sR = document.getElementById('hmproSettingMegaShowRoot');
				comp.settings.menu_id = mId ? parseInt(mId.value || '0', 10) : 0;
				comp.settings.root_item_id = rId ? parseInt(rId.value || '0', 10) : 0;
				comp.settings.max_depth = dId ? parseInt(dId.value || '2', 10) : 2;
				comp.settings.show_root_title = sR && sR.checked ? 1 : 0;
			}
			if (type === 'footer_menu') {
				var fmId = document.getElementById('hmproSettingFooterMenuId');
				var fmShow = document.getElementById('hmproSettingFooterMenuShowTitle');
				comp.settings.menu_id = fmId ? parseInt(fmId.value || '0', 10) : 0;
				comp.settings.show_title = fmShow && fmShow.checked ? 1 : 0;
			}
			if (type === 'footer_info' || type === 'footer-info') {
				var fiTitle = document.getElementById('hmproSettingFooterInfoTitle');
				var fiLines = document.getElementById('hmproSettingFooterInfoLines');
				comp.settings.title = fiTitle ? (fiTitle.value || '') : (comp.settings.title || '');
				comp.settings.lines = fiLines ? (fiLines.value || '') : (comp.settings.lines || '');
			}
			if (type === 'menu' || type === 'header_menu' || type === 'primary_menu') {
				var sel = document.getElementById('hmproSettingMenuLocation');
				if (sel) comp.settings.location = sel.value;
			}
			if (type === 'search') {
				var inp = document.getElementById('hmproSettingSearchPlaceholder');
				if (inp) comp.settings.placeholder = inp.value;
			}
			if (type === 'button') {
				var t = document.getElementById('hmproSettingButtonText');
				var u = document.getElementById('hmproSettingButtonUrl');
				if (t) comp.settings.text = t.value;
				if (u) comp.settings.url = u.value;
			}
			if (type === 'html') {
				var ta = document.getElementById('hmproSettingHtmlContent');
				if (ta) comp.settings.content = ta.value;
			}
			if (type === 'spacer') {
				var w = document.getElementById('hmproSettingSpacerWidth');
				var h = document.getElementById('hmproSettingSpacerHeight');
				if (w) comp.settings.width = w.value;
				if (h) comp.settings.height = h.value;
			}
			if (type === 'social') {
				comp.settings.urls = comp.settings.urls || {};
				var keys = ['facebook', 'instagram', 'x', 'youtube', 'tiktok', 'linkedin', 'whatsapp', 'telegram'];
				keys.forEach(function (k) {
					var el = document.getElementById('hmproSettingSocial_' + k);
					if (!el) return;
					var val = (el.value || '').trim();
					if (val) comp.settings.urls[k] = val;
					else delete comp.settings.urls[k];
				});
				var sz = document.getElementById('hmproSettingSocialSize');
				var gp = document.getElementById('hmproSettingSocialGap');
				var nt = document.getElementById('hmproSettingSocialNewTab');
				comp.settings.size = sz ? sz.value : (comp.settings.size || 'normal');
				comp.settings.gap = gp ? gp.value : (comp.settings.gap || 'normal');
				comp.settings.new_tab = nt ? !!nt.checked : !!comp.settings.new_tab;
			}
			if (type === 'social_icon_button') {
				var urlEl = document.getElementById('hmproSettingSocialIconUrl');
				var ntEl = document.getElementById('hmproSettingSocialIconNewTab');
				var trEl = document.getElementById('hmproSettingSocialIconTransparent');
				var modeEl = document.getElementById('hmproSettingSocialIconMode');
				var preEl = document.getElementById('hmproSettingSocialIconPreset');
				var cuEl = document.getElementById('hmproSettingSocialIconCustom');

				comp.settings.url = urlEl ? urlEl.value.trim() : '';
				comp.settings.new_tab = ntEl ? !!ntEl.checked : false;
				comp.settings.transparent = trEl ? !!trEl.checked : false;
				comp.settings.icon_mode = modeEl ? modeEl.value : 'preset';
				comp.settings.icon_preset = preEl ? preEl.value : 'facebook';
				comp.settings.custom_icon = cuEl ? cuEl.value : '';
			}

			syncLayoutToField();
			sync();
			render();
			closeModal();
		});
	}

	if (builderForm) {
		builderForm.addEventListener('submit', function () {
			if (window.__hmproLayoutDirty) {
				syncLayoutToField();
				window.__hmproLayoutDirty = false;
			}
		});

		builderForm.addEventListener('submit', function (event) {
			if (isAutoSubmit) return;
			if (!modal || !modalSave || !activeEditing) return;
			var isOpen = modal.classList.contains('is-open') || modal.getAttribute('aria-hidden') === 'false';
			if (!isOpen) return;

			event.preventDefault();
			isAutoSubmit = true;
			modalSave.click();
			window.setTimeout(function () {
				if (builderForm.requestSubmit) {
					builderForm.requestSubmit();
				} else {
					builderForm.submit();
				}
				window.setTimeout(function () {
					isAutoSubmit = false;
				}, 0);
			}, 0);
		});
	}

	var firstSectionBtn = document.querySelector('.hmpro-builder-section-btn[data-section="' + activeSection + '"]');
	if (firstSectionBtn) firstSectionBtn.classList.add('button-primary');
	ensureSingleRowCols(activeSection);
	render();
	sync();
}());
