(function () {
	'use strict';

	var data = window.hmproBuilderData || {};
	var layoutField = document.getElementById('hmproBuilderLayoutField');
	var sectionButtons = document.querySelectorAll('.hmpro-builder-section-btn');
	var elementButtons = document.querySelectorAll('.hmpro-builder-element-btn');
	var editingLabel = document.querySelector('.hmpro-builder-editing-key');

	var zoneLeft = document.getElementById('hmproZoneLeft');
	var zoneCenter = document.getElementById('hmproZoneCenter');
	var zoneRight = document.getElementById('hmproZoneRight');

	var modal = document.getElementById('hmproCompModal');
	var modalBody = document.getElementById('hmproModalBody');
	var modalSave = document.getElementById('hmproModalSave');
	var builderForm = layoutField ? layoutField.form : null;
	var isAutoSubmit = false;

	if (!layoutField || !zoneLeft || !zoneCenter || !zoneRight) return;

	var layout;
	try {
		layout = JSON.parse(layoutField.value || '{}');
	} catch (e) {
		layout = data.layout || { schema_version: 1, regions: {} };
	}

	var activeSection = (data.area === 'footer') ? 'footer_top' : 'header_top';
	var activeEditing = null;

	var ZONES = ['left', 'center', 'right'];

	function uid(prefix) {
		return prefix + '_' + Math.random().toString(36).slice(2, 8) + '_' + Date.now().toString(36).slice(2, 6);
	}

	function ensureRegion(sectionKey) {
		layout.schema_version = 1;
		layout.regions = layout.regions || {};
		layout.regions[sectionKey] = layout.regions[sectionKey] || [];
	}

	function ensureSingleRow3Cols(sectionKey) {
		ensureRegion(sectionKey);
		if (!layout.regions[sectionKey].length) {
			layout.regions[sectionKey].push({
				id: uid('row'),
				columns: [
					{ id: uid('col_left'), width: 4, components: [] },
					{ id: uid('col_center'), width: 4, components: [] },
					{ id: uid('col_right'), width: 4, components: [] }
				]
			});
			return;
		}

		var row = layout.regions[sectionKey][0];
		if (!row || !Array.isArray(row.columns) || row.columns.length === 0) {
			layout.regions[sectionKey] = [{
				id: uid('row'),
				columns: [
					{ id: uid('col_left'), width: 4, components: [] },
					{ id: uid('col_center'), width: 4, components: [] },
					{ id: uid('col_right'), width: 4, components: [] }
				]
			}];
			return;
		}

		if (row.columns.length === 1) {
			var existing = row.columns[0].components || [];
			row.columns = [
				{ id: uid('col_left'), width: 4, components: [] },
				{ id: uid('col_center'), width: 4, components: existing },
				{ id: uid('col_right'), width: 4, components: [] }
			];
		}

		while (row.columns.length < 3) {
			row.columns.push({ id: uid('col_extra'), width: 4, components: [] });
		}
		row.columns = row.columns.slice(0, 3);
		row.columns[0].width = 4;
		row.columns[1].width = 4;
		row.columns[2].width = 4;

		for (var i = 0; i < 3; i++) {
			row.columns[i].components = Array.isArray(row.columns[i].components) ? row.columns[i].components : [];
		}
	}

	function getZoneListEl(zone) {
		if (zone === 'left') return zoneLeft;
		if (zone === 'center') return zoneCenter;
		return zoneRight;
	}

	function getComponents(sectionKey, zone) {
		ensureSingleRow3Cols(sectionKey);
		var row = layout.regions[sectionKey][0];
		var idx = ZONES.indexOf(zone);
		return row.columns[idx].components;
	}

	function setComponents(sectionKey, zone, comps) {
		ensureSingleRow3Cols(sectionKey);
		var row = layout.regions[sectionKey][0];
		var idx = ZONES.indexOf(zone);
		row.columns[idx].components = comps;
	}

	function findComponentById(sectionKey, compId) {
		if (!compId) return null;
		ensureSingleRow3Cols(sectionKey);
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

	function sync() {
		layoutField.value = JSON.stringify(layout);
	}

	function titleize(type) {
		var t = (type || '').toString();
		return t.charAt(0).toUpperCase() + t.slice(1);
	}

	function render() {
		ensureSingleRow3Cols(activeSection);

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
				pill.textContent = titleize(comp.type);

				var tw = document.createElement('div');
				tw.className = 'hmpro-titlewrap';
				var strong = document.createElement('strong');
				strong.textContent = titleize(comp.type);
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
		var zone = 'center';
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

		var type = (comp.type || '').toLowerCase();
		var settings = comp.settings || {};

		if (type === 'menu') {
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
			var comps = getComponents(sectionKey, zone).slice();
			var comp = match.comp;
			comp.settings = comp.settings || {};

			var type = (comp.type || '').toLowerCase();
			if (type === 'menu') {
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
				var keys = ['facebook','instagram','x','youtube','tiktok','linkedin','whatsapp','telegram'];
				keys.forEach(function(k){
					var el = document.getElementById('hmproSettingSocial_' + k);
					if (!el) return;
					var val = (el.value || '').trim();
					if (val) {
						comp.settings.urls[k] = val;
					} else {
						delete comp.settings.urls[k];
					}
				});
				var sz = document.getElementById('hmproSettingSocialSize');
				var gp = document.getElementById('hmproSettingSocialGap');
				var nt = document.getElementById('hmproSettingSocialNewTab');
				comp.settings.size = sz ? sz.value : (comp.settings.size || 'normal');
				comp.settings.gap = gp ? gp.value : (comp.settings.gap || 'normal');
				comp.settings.new_tab = nt ? !!nt.checked : !!comp.settings.new_tab;
			}

			comps[index] = comp;
			setComponents(sectionKey, zone, comps);
			sync();
			render();
			closeModal();
		});
	}

	if (builderForm) {
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
	ensureSingleRow3Cols(activeSection);
	render();
	sync();
}());
