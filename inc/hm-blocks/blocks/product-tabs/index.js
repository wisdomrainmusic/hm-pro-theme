(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n;

  const { InspectorControls } = wp.blockEditor;

  const {
    PanelBody,
    ToggleControl,
    RangeControl,
    TextControl,
    SelectControl,
    Button,
    ButtonGroup,
    Notice,
  } = wp.components;

  const ServerSideRender = wp.serverSideRender;

  function clampInt(value, min, max) {
    const n = parseInt(value, 10);
    if (Number.isNaN(n)) return min;
    return Math.max(min, Math.min(max, n));
  }

  registerBlockType("hmpro/product-tabs", {
    title: __("HM Product Tabs", "hm-pro-theme"),
    icon: "screenoptions",
    category: "hmpro",
    description: __("Tabbed WooCommerce product grid with optional full width layout.", "hm-pro-theme"),
    supports: { html: false },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const {
        fullWidth,
        columnsDesktop,
        gridGap,
        tabsAlign,
        tabs = [],
      } = attributes;

      const updateTab = (index, patch) => {
        const next = [...tabs];
        next[index] = Object.assign({}, next[index], patch);
        setAttributes({ tabs: next });
      };

      const addTab = () => {
        const next = [
          ...tabs,
          { title: `Tab ${tabs.length + 1}`, queryType: "category", termId: 0, perPage: 12 },
        ];
        setAttributes({ tabs: next });
      };

      const removeTab = (index) => {
        const next = tabs.filter((_, i) => i !== index);
        setAttributes({
          tabs: next.length ? next : [{ title: "Tab 1", queryType: "category", termId: 0, perPage: 12 }],
        });
      };

      const moveTab = (from, to) => {
        if (to < 0 || to >= tabs.length) return;
        const next = [...tabs];
        const item = next.splice(from, 1)[0];
        next.splice(to, 0, item);
        setAttributes({ tabs: next });
      };

      return (
        <>
          <InspectorControls>
            <PanelBody title={__("Layout", "hm-pro-theme")} initialOpen={true}>
              <ToggleControl
                label={__("Full Width (Hero Like)", "hm-pro-theme")}
                checked={!!fullWidth}
                onChange={(v) => setAttributes({ fullWidth: !!v })}
              />

              <RangeControl
                label={__("Cards Per View (Desktop)", "hm-pro-theme")}
                value={columnsDesktop}
                onChange={(v) => setAttributes({ columnsDesktop: clampInt(v, 2, 6) })}
                min={2}
                max={6}
              />

              <RangeControl
                label={__("Grid Gap (px)", "hm-pro-theme")}
                value={gridGap}
                onChange={(v) => setAttributes({ gridGap: clampInt(v, 0, 80) })}
                min={0}
                max={80}
              />

              <SelectControl
                label={__("Tabs Alignment", "hm-pro-theme")}
                value={tabsAlign}
                options={[
                  { label: __("Left", "hm-pro-theme"), value: "flex-start" },
                  { label: __("Center", "hm-pro-theme"), value: "center" },
                  { label: __("Right", "hm-pro-theme"), value: "flex-end" },
                ]}
                onChange={(v) => setAttributes({ tabsAlign: v })}
              />
            </PanelBody>

            <PanelBody title={__("Tabs", "hm-pro-theme")} initialOpen={true}>
              <Notice status="info" isDismissible={false}>
                {__("Commit 1: Term selection uses Term ID. Commit 2 will replace this with searchable selectors.", "hm-pro-theme")}
              </Notice>

              {(tabs || []).map((tab, index) => (
                <div key={index} style={{ padding: "12px 0", borderTop: "1px solid rgba(0,0,0,0.08)" }}>
                  <TextControl
                    label={__("Tab Title", "hm-pro-theme")}
                    value={tab.title || ""}
                    onChange={(v) => updateTab(index, { title: v })}
                  />

                  <SelectControl
                    label={__("Query Type", "hm-pro-theme")}
                    value={tab.queryType || "category"}
                    options={[
                      { label: __("By Category", "hm-pro-theme"), value: "category" },
                      { label: __("By Tag", "hm-pro-theme"), value: "tag" },
                    ]}
                    onChange={(v) => updateTab(index, { queryType: v })}
                  />

                  <TextControl
                    label={__("Term ID (category/tag)", "hm-pro-theme")}
                    value={String(tab.termId ?? 0)}
                    onChange={(v) => updateTab(index, { termId: clampInt(v, 0, 999999) })}
                  />

                  <RangeControl
                    label={__("Products Per Tab (Max 24)", "hm-pro-theme")}
                    value={tab.perPage ?? 12}
                    onChange={(v) => updateTab(index, { perPage: clampInt(v, 1, 24) })}
                    min={1}
                    max={24}
                  />

                  <ButtonGroup>
                    <Button variant="secondary" onClick={() => moveTab(index, index - 1)} disabled={index === 0}>
                      {__("Up", "hm-pro-theme")}
                    </Button>
                    <Button
                      variant="secondary"
                      onClick={() => moveTab(index, index + 1)}
                      disabled={index === (tabs.length - 1)}
                    >
                      {__("Down", "hm-pro-theme")}
                    </Button>
                    <Button variant="secondary" isDestructive onClick={() => removeTab(index)}>
                      {__("Remove", "hm-pro-theme")}
                    </Button>
                  </ButtonGroup>
                </div>
              ))}

              <div style={{ paddingTop: 12 }}>
                <Button variant="primary" onClick={addTab}>
                  {__("Add Tab", "hm-pro-theme")}
                </Button>
              </div>
            </PanelBody>
          </InspectorControls>

          <div className="hmpro-pft__editor">
            <ServerSideRender block="hmpro/product-tabs" attributes={attributes} />
          </div>
        </>
      );
    },

    save: function () {
      return null; // dynamic render.php
    },
  });
})(window.wp);
