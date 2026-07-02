import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  RangeControl,
  SelectControl,
  ComboboxControl,
  Button,
  Spinner,
  BaseControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/* Searchable, reorderable picker for specific Game posts. */
function GamePicker({ games, setAttributes }) {
  const [search, setSearch] = useState('');

  // Search suggestions (title match), excluding already-picked games.
  const suggestions = useSelect(
    (select) => {
      const query = { per_page: 20, orderby: 'title', order: 'asc', _fields: 'id,title' };
      if (search) query.search = search;
      const recs = select(coreStore).getEntityRecords('postType', 'game', query);
      return recs || [];
    },
    [search]
  );

  // Resolve the picked IDs to records, preserving the chosen order.
  const { selected, resolving } = useSelect(
    (select) => {
      if (!games.length) return { selected: [], resolving: false };
      const query = { include: games, per_page: games.length, _fields: 'id,title', orderby: 'include' };
      const recs = select(coreStore).getEntityRecords('postType', 'game', query);
      const isResolving = select(coreStore).isResolving('getEntityRecords', ['postType', 'game', query]);
      const ordered = recs ? games.map((id) => recs.find((r) => r.id === id)).filter(Boolean) : [];
      return { selected: ordered, resolving: isResolving };
    },
    [games]
  );

  const options = suggestions
    .filter((g) => !games.includes(g.id))
    .map((g) => ({ value: String(g.id), label: g.title.rendered || `#${g.id}` }));

  const addGame = (value) => {
    const id = parseInt(value, 10);
    if (id && !games.includes(id)) setAttributes({ games: [...games, id] });
    setSearch('');
  };

  const removeGame = (id) => setAttributes({ games: games.filter((g) => g !== id) });

  const move = (index, dir) => {
    const next = index + dir;
    if (next < 0 || next >= games.length) return;
    const arr = [...games];
    [arr[index], arr[next]] = [arr[next], arr[index]];
    setAttributes({ games: arr });
  };

  return (
    <BaseControl label={__('Games', 'solaire')} help={__('Search to add games. Drag order sets the ranking (top = #1).', 'solaire')}>
      <ComboboxControl
        __next40pxDefaultSize
        value={null}
        options={options}
        onChange={addGame}
        onFilterValueChange={setSearch}
        placeholder={__('Search games…', 'solaire')}
      />
      {resolving && !selected.length ? (
        <Spinner />
      ) : (
        <ul style={{ margin: '8px 0 0', padding: 0, listStyle: 'none' }}>
          {selected.map((g, i) => (
            <li
              key={g.id}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '4px',
                padding: '4px 6px',
                border: '1px solid #e0e0e0',
                borderRadius: '2px',
                marginBottom: '4px',
              }}
            >
              <span style={{ width: '1.5em', color: '#757575', fontVariantNumeric: 'tabular-nums' }}>{i + 1}.</span>
              <span style={{ flex: 1, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {g.title.rendered || `#${g.id}`}
              </span>
              <Button icon="arrow-up-alt2" label={__('Move up', 'solaire')} disabled={i === 0} onClick={() => move(i, -1)} size="small" />
              <Button icon="arrow-down-alt2" label={__('Move down', 'solaire')} disabled={i === selected.length - 1} onClick={() => move(i, 1)} size="small" />
              <Button icon="no-alt" label={__('Remove', 'solaire')} isDestructive onClick={() => removeGame(g.id)} size="small" />
            </li>
          ))}
          {!selected.length && (
            <li style={{ color: '#757575', fontStyle: 'italic' }}>{__('No games selected yet.', 'solaire')}</li>
          )}
        </ul>
      )}
    </BaseControl>
  );
}

export default function Edit({ attributes, setAttributes }) {
  const { title, source, games, category, count, perPage, countMobile, perPageMobile, viewAllUrl } = attributes;
  const isManual = source === 'manual';

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Ranking List', 'solaire')} initialOpen>
          <TextControl label={__('Title', 'solaire')} value={title} onChange={(v) => setAttributes({ title: v })} />
          <SelectControl
            label={__('Games source', 'solaire')}
            value={source}
            options={[
              { label: __('Automatic (by category)', 'solaire'), value: 'auto' },
              { label: __('Manual (pick games)', 'solaire'), value: 'manual' },
            ]}
            onChange={(v) => setAttributes({ source: v })}
          />
          {isManual ? (
            <GamePicker games={games} setAttributes={setAttributes} />
          ) : (
            <TextControl
              label={__('Category slug (optional)', 'solaire')}
              value={category}
              onChange={(v) => setAttributes({ category: v })}
            />
          )}
          <TextControl label={__('"View all" URL', 'solaire')} value={viewAllUrl} onChange={(v) => setAttributes({ viewAllUrl: v })} />
        </PanelBody>
        <PanelBody title={__('Desktop layout', 'solaire')} initialOpen={false}>
          <RangeControl label={__('Number of rows', 'solaire')} min={3} max={24} value={count} onChange={(v) => setAttributes({ count: v })} />
          <RangeControl label={__('Rows per slide', 'solaire')} min={1} max={12} value={perPage} onChange={(v) => setAttributes({ perPage: v })} />
        </PanelBody>
        <PanelBody title={__('Mobile layout', 'solaire')} initialOpen={false}>
          <RangeControl label={__('Number of rows (mobile)', 'solaire')} min={1} max={24} value={countMobile} onChange={(v) => setAttributes({ countMobile: v })} />
          <RangeControl label={__('Rows per slide (mobile)', 'solaire')} min={1} max={12} value={perPageMobile} onChange={(v) => setAttributes({ perPageMobile: v })} />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <ServerSideRender block="solaire/ranking-list" attributes={attributes} />
      </div>
    </>
  );
}
