/**
 * Shared editor controls for Solaire blocks.
 * Not a block itself (no block.json) — imported by block edit.js files.
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
  Button,
  TextControl,
  TextareaControl,
  SelectControl,
  BaseControl,
} from '@wordpress/components';

/** A simple media picker that stores { id, url } back via onChange. */
export function MediaField({ label, value, onChange }) {
  return (
    <BaseControl label={label} __nextHasNoMarginBottom>
      <MediaUploadCheck>
        <MediaUpload
          onSelect={(media) => onChange({ id: media.id, url: media.url })}
          allowedTypes={['image']}
          value={value?.id}
          render={({ open }) => (
            <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
              {value?.url ? (
                <img
                  src={value.url}
                  alt=""
                  style={{ width: 56, height: 56, objectFit: 'cover', borderRadius: 6 }}
                />
              ) : null}
              <Button variant="secondary" onClick={open}>
                {value?.url ? __('Replace', 'solaire') : __('Select image', 'solaire')}
              </Button>
              {value?.url ? (
                <Button variant="tertiary" isDestructive onClick={() => onChange({ id: 0, url: '' })}>
                  {__('Remove', 'solaire')}
                </Button>
              ) : null}
            </div>
          )}
        />
      </MediaUploadCheck>
    </BaseControl>
  );
}

/**
 * Generic repeater.
 *
 * @param {Array}    items     Current array of objects.
 * @param {Function} onChange  Receives the new array.
 * @param {Array}    fields    [{ name, label, type: 'text'|'textarea'|'media'|'select', options }]
 * @param {Object}   newItem   Template object for a freshly added row.
 * @param {string}   addLabel
 */
export function Repeater({ items = [], onChange, fields, newItem, addLabel }) {
  const update = (index, name, val) => {
    const next = items.map((it, i) => (i === index ? { ...it, [name]: val } : it));
    onChange(next);
  };
  const remove = (index) => onChange(items.filter((_, i) => i !== index));
  const move = (index, dir) => {
    const target = index + dir;
    if (target < 0 || target >= items.length) return;
    const next = items.slice();
    [next[index], next[target]] = [next[target], next[index]];
    onChange(next);
  };
  const add = () => onChange([...items, { ...newItem }]);

  return (
    <Fragment>
      {items.map((item, index) => (
        <div
          key={index}
          style={{
            border: '1px solid #e0e0e0',
            borderRadius: 6,
            padding: 12,
            marginBottom: 12,
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
            <strong>{`#${index + 1}`}</strong>
            <div style={{ display: 'flex', gap: 4 }}>
              <Button size="small" icon="arrow-up-alt2" label="Move up" onClick={() => move(index, -1)} />
              <Button size="small" icon="arrow-down-alt2" label="Move down" onClick={() => move(index, 1)} />
              <Button size="small" isDestructive icon="trash" label="Remove" onClick={() => remove(index)} />
            </div>
          </div>
          {fields.map((f) => {
            const val = item[f.name];
            if (f.type === 'textarea') {
              return (
                <TextareaControl
                  key={f.name}
                  label={f.label}
                  value={val || ''}
                  onChange={(v) => update(index, f.name, v)}
                  __nextHasNoMarginBottom
                />
              );
            }
            if (f.type === 'media') {
              return (
                <MediaField
                  key={f.name}
                  label={f.label}
                  value={val || { id: 0, url: '' }}
                  onChange={(v) => update(index, f.name, v)}
                />
              );
            }
            if (f.type === 'select') {
              return (
                <SelectControl
                  key={f.name}
                  label={f.label}
                  value={val || ''}
                  options={f.options}
                  onChange={(v) => update(index, f.name, v)}
                  __nextHasNoMarginBottom
                />
              );
            }
            return (
              <TextControl
                key={f.name}
                label={f.label}
                value={val || ''}
                onChange={(v) => update(index, f.name, v)}
                __nextHasNoMarginBottom
              />
            );
          })}
        </div>
      ))}
      <Button variant="primary" onClick={add}>
        {addLabel || __('Add item', 'solaire')}
      </Button>
    </Fragment>
  );
}
