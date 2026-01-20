import { __ } from "@wordpress/i18n";
import { InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  ToggleControl,
  RangeControl,
  TextControl,
  SelectControl,
  Button,
  ButtonGroup,
  Notice,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

function clampInt(value, min, max) {
  const n = parseInt(value, 10);
  if (Number.isNaN(n)) return min;
  return Math.max(min, Math.min(max, n));
}

export default function Edit({ attributes, setAttributes }) {
  const {
    fullWidth,
    columnsDesktop,
    gridGap,
    tabsAlign,
    tabs = [],
  } = attributes;

  const updateTab = (index, patch) => {
    const next = [...tabs];
    next[index] = { ...next[index], ...patch };
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
    setAttributes({ tabs: next.length ? next : [{ title: "Tab 1", queryType: "category", termId: 0, perPage: 12 }] });
  };

  const moveTab = (from, to) => {
    if (to < 0 || to >= tabs.length) return;
    const next = [...tabs];
    const [item] = next.splice(from, 1);
    next.splice(to, 0, item);
    setAttributes({ tabs: next });
  };

  const hasWooHint = (
    <Notice status="info" isDismissible={false}>
      {__("This block renders products on the frontend. In the editor, it uses server-side preview. If WooCommerce is not active, it will show an informational message.", "hm-pro-theme")}
    </Notice>
  );

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
          {hasWooHint}

          {tabs.map((tab, index) => (
            <div key={index} style={{ padding: "12px 0", borderTop: "1px solid rgba(255,255,255,0.08)" }}>
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
                help={__("Temporary input for Commit 1. We'll upgrade to a searchable term selector in Commit 2.", "hm-pro-theme")}
              />

              <RangeControl
                label={__("Products Per Tab (Max 24)", "hm-pro-theme")}
                value={tab.perPage ?? 12}
                onChange={(v) => updateTab(index, { perPage: clampInt(v, 1, 24) })}
                min={1}
                max={24}
              />

              <ButtonGroup>
                <Button
                  variant="secondary"
                  onClick={() => moveTab(index, index - 1)}
                  disabled={index === 0}
                >
                  {__("Up", "hm-pro-theme")}
                </Button>
                <Button
                  variant="secondary"
                  onClick={() => moveTab(index, index + 1)}
                  disabled={index === tabs.length - 1}
                >
                  {__("Down", "hm-pro-theme")}
                </Button>
                <Button
                  variant="secondary"
                  isDestructive
                  onClick={() => removeTab(index)}
                >
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
}
