import {
  Sparkles, Zap, Shield, Star, Compass, Code, Layers, PenTool,
  Gauge, Users, Mail, Phone, MapPin, Clock, HelpCircle, Box,
} from 'lucide-react';
import Editable from './Editable';
import { setPath } from '../../lib/editorSchema';

// Map the icon names stored in section data to lucide components.
const ICONS = {
  sparkles: Sparkles, zap: Zap, shield: Shield, star: Star, compass: Compass,
  code: Code, layers: Layers, 'pen-tool': PenTool, gauge: Gauge, users: Users,
};
const iconFor = (name) => ICONS[name] || Box;

/**
 * Renders one section as a visual block for the preview canvas. Text fields are
 * inline-editable; `update` patches the whole section, `updateData` patches the
 * section's `data` object at a dotted path.
 */
export default function SectionRenderer({ section, template, update, updateData }) {
  const d = section.data || {};
  const crystal = template === 'crystal';
  const setData = (path, value) => updateData(setPath(d, path, value));
  const setItem = (listKey, index, path, value) => {
    const list = [...(d[listKey] || [])];
    list[index] = setPath(list[index], path, value);
    updateData({ ...d, [listKey]: list });
  };

  switch (section.type) {
    case 'hero': {
      const align = d.alignment || 'center';
      const alignClass = align === 'left' ? 'text-left items-start' : 'text-center items-center';
      const hasBg = !!d.background_image;
      if (crystal) {
        return (
          <div className="overflow-hidden bg-[#f1f2f4] px-8 py-12 lg:px-14">
            <div className="mx-auto grid max-w-6xl items-center gap-10 lg:grid-cols-[1.05fr_.95fr]">
              <div>
                <Editable as="p" value={d.subheading} onChange={(v) => setData('subheading', v)} placeholder="Eyebrow" className="mb-4 text-sm font-semibold text-[#d5545e]" />
                <Editable as="h1" value={section.title} onChange={(v) => update({ title: v })} placeholder="Headline" className="max-w-xl text-4xl font-bold leading-tight text-[#202534] lg:text-5xl" />
                <Editable as="p" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Supporting text" className="mt-5 max-w-xl text-base leading-7 text-[#626777]" />
                {d.primary_cta && <Editable as="span" value={d.primary_cta.label} onChange={(v) => setData('primary_cta.label', v)} placeholder="Button" className="mt-7 inline-block rounded-md bg-[#d5545e] px-6 py-3 font-semibold text-white" />}
              </div>
              {d.image && <img src={d.image} alt={d.image_alt || ''} className="mx-auto max-h-[360px] w-full object-contain" />}
            </div>
          </div>
        );
      }
      return (
        <div
          className="relative overflow-hidden rounded-xl border border-gray-200"
          style={hasBg ? { backgroundImage: `url(${d.background_image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined}
        >
          <div className={`${hasBg ? 'bg-slate-900/60' : 'bg-gradient-to-br from-slate-50 to-slate-100'}`}>
            <div className={`flex flex-col ${alignClass} gap-4 px-8 py-20 max-w-3xl ${align === 'center' ? 'mx-auto' : ''}`}>
              <Editable
                as="p"
                value={d.subheading}
                onChange={(v) => setData('subheading', v)}
                placeholder="Eyebrow"
                className={`text-sm font-semibold uppercase tracking-wide ${hasBg ? 'text-neutral-400' : 'text-black'}`}
              />
              <Editable
                as="h1"
                value={section.title}
                onChange={(v) => update({ title: v })}
                placeholder="Headline"
                className={`text-4xl font-bold leading-tight ${hasBg ? 'text-white' : 'text-gray-900'}`}
              />
              <Editable
                as="p"
                multiline
                value={section.content}
                onChange={(v) => update({ content: v })}
                placeholder="Supporting text"
                className={`text-lg ${hasBg ? 'text-slate-200' : 'text-gray-600'}`}
              />
              <div className={`flex gap-3 mt-2 ${align === 'center' ? 'justify-center' : ''}`}>
                {d.primary_cta && (
                  <Editable
                    as="span"
                    value={d.primary_cta.label}
                    onChange={(v) => setData('primary_cta.label', v)}
                    placeholder="Button"
                    className="inline-block px-5 py-2.5 rounded-lg bg-black text-white font-medium"
                  />
                )}
                {d.secondary_cta && (
                  <Editable
                    as="span"
                    value={d.secondary_cta.label}
                    onChange={(v) => setData('secondary_cta.label', v)}
                    placeholder="Button"
                    className={`inline-block px-5 py-2.5 rounded-lg font-medium border ${hasBg ? 'border-white/40 text-white' : 'border-gray-300 text-gray-700'}`}
                  />
                )}
              </div>
            </div>
          </div>
        </div>
      );
    }

    case 'text':
      if (crystal) {
        return (
          <div className="mx-auto grid max-w-6xl items-center gap-10 px-8 py-14 lg:grid-cols-2">
            {d.image && <img src={d.image} alt={d.image_alt || ''} className="h-80 w-full rounded-md object-cover" />}
            <div>
              <Editable as="p" value={d.eyebrow} onChange={(v) => setData('eyebrow', v)} placeholder="Eyebrow" className="mb-3 text-sm font-semibold text-[#d5545e]" />
              <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-3xl font-bold leading-tight text-[#202534]" />
              <Editable as="div" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Write your content…" className="mt-5 whitespace-pre-wrap leading-7 text-[#626777]" />
            </div>
          </div>
        );
      }
      return (
        <div className="max-w-3xl mx-auto px-8 py-14">
          <Editable
            as="h2"
            value={section.title}
            onChange={(v) => update({ title: v })}
            placeholder="Heading"
            className="text-2xl font-bold text-gray-900 mb-4"
          />
          <Editable
            as="div"
            multiline
            value={section.content}
            onChange={(v) => update({ content: v })}
            placeholder="Write your content…"
            className="text-gray-600 leading-relaxed whitespace-pre-wrap"
          />
        </div>
      );

    case 'features': {
      const cols = Number(d.columns) === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3';
      const items = d.items || [];
      if (crystal) {
        return (
          <div className="bg-white px-8 py-14">
            <div className="mx-auto max-w-6xl">
              <Editable as="p" value={d.eyebrow} onChange={(v) => setData('eyebrow', v)} placeholder="Eyebrow" className="mb-3 text-center text-sm font-semibold text-[#d5545e]" />
              <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Section heading" className="mx-auto mb-9 max-w-3xl text-center text-3xl font-bold leading-tight text-[#202534]" />
              <div className={`grid grid-cols-1 ${cols} gap-5`}>
                {items.map((item, i) => (
                  <article key={i} className="overflow-hidden rounded-md bg-[#f4f4f5]">
                    {item.image && <img src={item.image} alt="" className="h-48 w-full object-cover" />}
                    <div className="p-5">
                      <Editable as="h3" value={item.title} onChange={(v) => setItem('items', i, 'title', v)} placeholder="Feature title" className="text-lg font-bold text-[#202534]" />
                      <Editable as="p" multiline value={item.description} onChange={(v) => setItem('items', i, 'description', v)} placeholder="Description" className="mt-2 text-sm leading-6 text-[#626777]" />
                      {item.url && <Editable as="span" value={item.link_label} onChange={(v) => setItem('items', i, 'link_label', v)} placeholder="Link label" className="mt-4 inline-block text-sm font-semibold text-[#d5545e]" />}
                    </div>
                  </article>
                ))}
              </div>
            </div>
          </div>
        );
      }
      return (
        <div className="px-8 py-14">
          <Editable
            as="h2"
            value={section.title}
            onChange={(v) => update({ title: v })}
            placeholder="Section heading"
            className="text-2xl font-bold text-gray-900 text-center mb-10"
          />
          <div className={`grid grid-cols-1 ${cols} gap-6 max-w-5xl mx-auto`}>
            {items.map((item, i) => {
              const Icon = iconFor(item.icon);
              return (
                <div key={i} className="rounded-xl border border-gray-200 bg-white p-6">
                  <div className="h-10 w-10 flex items-center justify-center rounded-lg bg-neutral-100 text-black mb-4">
                    <Icon className="h-5 w-5" />
                  </div>
                  <Editable
                    as="h3"
                    value={item.title}
                    onChange={(v) => setItem('items', i, 'title', v)}
                    placeholder="Feature title"
                    className="font-semibold text-gray-900 mb-1"
                  />
                  <Editable
                    as="p"
                    multiline
                    value={item.description}
                    onChange={(v) => setItem('items', i, 'description', v)}
                    placeholder="Description"
                    className="text-sm text-gray-600"
                  />
                </div>
              );
            })}
          </div>
        </div>
      );
    }

    case 'stats': {
      const items = d.items || [];
      return (
        <div className="px-8 py-14 bg-slate-50 rounded-xl">
          {section.title && (
            <Editable
              as="h2"
              value={section.title}
              onChange={(v) => update({ title: v })}
              placeholder="Heading"
              className="text-2xl font-bold text-gray-900 text-center mb-10"
            />
          )}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto text-center">
            {items.map((item, i) => (
              <div key={i}>
                <Editable
                  as="div"
                  value={item.value}
                  onChange={(v) => setItem('items', i, 'value', v)}
                  placeholder="0"
                  className="text-3xl font-bold text-black"
                />
                <Editable
                  as="div"
                  value={item.label}
                  onChange={(v) => setItem('items', i, 'label', v)}
                  placeholder="Label"
                  className="text-sm text-gray-600 mt-1"
                />
              </div>
            ))}
          </div>
        </div>
      );
    }

    case 'testimonials': {
      const items = d.items || [];
      return (
        <div className="px-8 py-14">
          <Editable
            as="h2"
            value={section.title}
            onChange={(v) => update({ title: v })}
            placeholder="Heading"
            className="text-2xl font-bold text-gray-900 text-center mb-10"
          />
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            {items.map((item, i) => (
              <figure key={i} className="rounded-xl border border-gray-200 bg-white p-6">
                <Editable
                  as="blockquote"
                  multiline
                  value={item.quote}
                  onChange={(v) => setItem('items', i, 'quote', v)}
                  placeholder="Quote"
                  className="text-gray-800 leading-relaxed"
                />
                <figcaption className="mt-4">
                  <Editable
                    as="div"
                    value={item.author}
                    onChange={(v) => setItem('items', i, 'author', v)}
                    placeholder="Author"
                    className="font-semibold text-gray-900 text-sm"
                  />
                  <Editable
                    as="div"
                    value={item.role}
                    onChange={(v) => setItem('items', i, 'role', v)}
                    placeholder="Role, Company"
                    className="text-xs text-gray-500"
                  />
                </figcaption>
              </figure>
            ))}
          </div>
        </div>
      );
    }

    case 'team': {
      const members = d.members || [];
      const initials = (name) => (name || '?').split(' ').map((p) => p[0]).slice(0, 2).join('').toUpperCase();
      return (
        <div className="px-8 py-14">
          <Editable
            as="h2"
            value={section.title}
            onChange={(v) => update({ title: v })}
            placeholder="Heading"
            className="text-2xl font-bold text-gray-900 text-center mb-10"
          />
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            {members.map((m, i) => (
              <div key={i} className="rounded-xl border border-gray-200 bg-white p-6 text-center">
                <div className="h-14 w-14 mx-auto rounded-full bg-neutral-100 text-black flex items-center justify-center font-semibold mb-3">
                  {initials(m.name)}
                </div>
                <Editable as="div" value={m.name} onChange={(v) => setItem('members', i, 'name', v)} placeholder="Name" className="font-semibold text-gray-900" />
                <Editable as="div" value={m.role} onChange={(v) => setItem('members', i, 'role', v)} placeholder="Role" className="text-sm text-black mb-2" />
                <Editable as="p" multiline value={m.bio} onChange={(v) => setItem('members', i, 'bio', v)} placeholder="Short bio" className="text-sm text-gray-600" />
              </div>
            ))}
          </div>
        </div>
      );
    }

    case 'faq': {
      const items = d.items || [];
      return (
        <div className="max-w-3xl mx-auto px-8 py-14">
          <Editable
            as="h2"
            value={section.title}
            onChange={(v) => update({ title: v })}
            placeholder="Heading"
            className="text-2xl font-bold text-gray-900 text-center mb-8"
          />
          <div className="divide-y divide-gray-200 border-t border-gray-200">
            {items.map((item, i) => (
              <div key={i} className="py-4 flex gap-3">
                <HelpCircle className="h-5 w-5 text-black shrink-0 mt-0.5" />
                <div className="flex-1">
                  <Editable as="div" value={item.question} onChange={(v) => setItem('items', i, 'question', v)} placeholder="Question" className="font-semibold text-gray-900" />
                  <Editable as="div" multiline value={item.answer} onChange={(v) => setItem('items', i, 'answer', v)} placeholder="Answer" className="text-sm text-gray-600 mt-1" />
                </div>
              </div>
            ))}
          </div>
        </div>
      );
    }

    case 'cta':
      if (crystal) {
        return (
          <div className="bg-[#202534] px-8 py-14 text-center">
            <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-3xl font-bold text-white" />
            <Editable as="p" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Supporting text" className="mx-auto mt-3 max-w-2xl text-[#d7d9df]" />
            {d.primary_cta && <Editable as="span" value={d.primary_cta.label} onChange={(v) => setData('primary_cta.label', v)} placeholder="Button" className="mt-6 inline-block rounded-md bg-[#d5545e] px-6 py-3 font-semibold text-white" />}
          </div>
        );
      }
      return (
        <div className="px-8 py-16">
          <div className="max-w-3xl mx-auto rounded-2xl bg-slate-900 px-8 py-12 text-center">
            <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-2xl font-bold text-white mb-3" />
            <Editable as="p" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Supporting text" className="text-slate-300 mb-6" />
            {d.primary_cta && (
              <Editable
                as="span"
                value={d.primary_cta.label}
                onChange={(v) => setData('primary_cta.label', v)}
                placeholder="Button"
                className="inline-block px-6 py-3 rounded-lg bg-black text-white font-medium"
              />
            )}
          </div>
        </div>
      );

    case 'contact':
      if (crystal) {
        return (
          <div className="mx-auto grid max-w-6xl items-center gap-10 px-8 py-14 lg:grid-cols-2">
            <div>
              <Editable as="p" value={d.eyebrow} onChange={(v) => setData('eyebrow', v)} placeholder="Eyebrow" className="mb-3 text-sm font-semibold text-[#d5545e]" />
              <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-3xl font-bold text-[#202534]" />
              <Editable as="p" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Supporting text" className="mt-3 text-[#626777]" />
              <div className="mt-6 space-y-3 text-sm text-[#202534]">
                {['email', 'phone', 'address', 'hours'].map((field) => <Editable key={field} as="div" value={d[field]} onChange={(v) => setData(field, v)} placeholder={field} />)}
              </div>
            </div>
            {d.image && <img src={d.image} alt={d.image_alt || ''} className="h-80 w-full rounded-md object-cover" />}
          </div>
        );
      }
      return (
        <div className="max-w-3xl mx-auto px-8 py-14">
          <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-2xl font-bold text-gray-900 mb-6" />
          <dl className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {[
              ['email', Mail, 'Email'],
              ['phone', Phone, 'Phone'],
              ['address', MapPin, 'Address'],
              ['hours', Clock, 'Hours'],
            ].map(([field, Icon, label]) => (
              <div key={field} className="flex gap-3">
                <Icon className="h-5 w-5 text-black shrink-0 mt-0.5" />
                <div>
                  <dt className="text-xs font-medium uppercase tracking-wide text-gray-400">{label}</dt>
                  <Editable as="dd" value={d[field]} onChange={(v) => setData(field, v)} placeholder={label} className="text-gray-800" />
                </div>
              </div>
            ))}
          </dl>
        </div>
      );

    case 'contact_form': {
      const fields = d.fields || [];
      return (
        <div className="max-w-xl mx-auto px-8 py-14">
          <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-2xl font-bold text-gray-900 mb-6" />
          <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
            {fields.length === 0 && (
              <p className="text-sm text-gray-400 text-center py-4">
                No fields yet. Add them in the Section panel →
              </p>
            )}
            {fields.map((f, i) => (
              <div key={i}>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  {f.label}{f.required ? ' *' : ''}
                </label>
                {f.type === 'textarea' ? (
                  <div className="w-full h-20 rounded-lg border border-gray-300 bg-gray-50" />
                ) : f.type === 'select' ? (
                  <div className="w-full h-10 rounded-lg border border-gray-300 bg-gray-50 flex items-center px-3 text-sm text-gray-400">
                    {(f.options && f.options[0]) || 'Select…'}
                  </div>
                ) : (
                  <div className="w-full h-10 rounded-lg border border-gray-300 bg-gray-50" />
                )}
              </div>
            ))}
            <div className="inline-block px-5 py-2.5 rounded-lg bg-black text-white font-medium text-sm">
              {d.button_label || 'Send message'}
            </div>
          </div>
        </div>
      );
    }

    case 'map':
      return (
        <div className="max-w-3xl mx-auto px-8 py-14">
          <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Heading" className="text-2xl font-bold text-gray-900 mb-6" />
          <div className="rounded-xl border border-gray-200 bg-slate-100 h-64 flex flex-col items-center justify-center gap-2 text-gray-500">
            <MapPin className="h-6 w-6" />
            <span className="text-sm font-medium">{d.label || 'Map location'}</span>
            <span className="text-xs">
              {Number(d.lat ?? 0).toFixed(4)}, {Number(d.lng ?? 0).toFixed(4)} · zoom {d.zoom ?? 14}
            </span>
          </div>
        </div>
      );

    default:
      return (
        <div className="max-w-3xl mx-auto px-8 py-10">
          <div className="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-400 mb-2">
              {section.type} section
            </p>
            <Editable as="h2" value={section.title} onChange={(v) => update({ title: v })} placeholder="Title" className="text-xl font-bold text-gray-900 mb-2" />
            <Editable as="div" multiline value={section.content} onChange={(v) => update({ content: v })} placeholder="Content" className="text-gray-600 whitespace-pre-wrap" />
          </div>
        </div>
      );
  }
}
