import { useEffect, useRef, useState } from 'react';
import { EditorContent, useEditor, useEditorState } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import {
  ArrowClockwise,
  ArrowCounterClockwise,
  Image as ImageIcon,
  LinkSimple,
  ListBullets,
  ListNumbers,
  Minus,
  Quotes,
  TextB,
  TextHThree,
  TextHTwo,
  TextItalic,
  TextStrikethrough,
  TextUnderline,
} from '../../lib/icons';
import MediaPicker from './MediaPicker';

/**
 * The post body editor: headings, emphasis, lists, quotes, links and images.
 * It reads and writes HTML. The server cleans that HTML again before it is
 * stored, so this only needs to offer what a post should contain.
 */
export default function RichTextEditor({ value, onChange, placeholder = 'Start writing…' }) {
  const [pickingImage, setPickingImage] = useState(false);
  const [linking, setLinking] = useState(false);
  const [href, setHref] = useState('');
  const linkInput = useRef(null);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: { levels: [2, 3] },
        link: { openOnClick: false, autolink: true, defaultProtocol: 'https' },
      }),
      Image,
      Placeholder.configure({ placeholder }),
    ],
    content: value || '',
    editorProps: { attributes: { class: 'rich-text', 'aria-label': 'Post body' } },
    onUpdate: ({ editor: current }) => onChange(current.isEmpty ? '' : current.getHTML()),
  });

  // A post loaded after the editor was made, or replaced from outside.
  useEffect(() => {
    if (!editor || editor.isFocused) return;
    if ((value || '') !== (editor.isEmpty ? '' : editor.getHTML())) {
      editor.commands.setContent(value || '', { emitUpdate: false });
    }
  }, [editor, value]);

  const state = useEditorState({
    editor,
    selector: ({ editor: current }) => (current ? {
      bold: current.isActive('bold'),
      italic: current.isActive('italic'),
      underline: current.isActive('underline'),
      strike: current.isActive('strike'),
      h2: current.isActive('heading', { level: 2 }),
      h3: current.isActive('heading', { level: 3 }),
      bulletList: current.isActive('bulletList'),
      orderedList: current.isActive('orderedList'),
      blockquote: current.isActive('blockquote'),
      link: current.isActive('link'),
      canUndo: current.can().undo(),
      canRedo: current.can().redo(),
    } : null),
  });

  useEffect(() => {
    if (linking) linkInput.current?.focus();
  }, [linking]);

  if (!editor || !state) return <div className="rich-text-shell min-h-[24rem]" />;

  const run = (command) => () => command(editor.chain().focus()).run();

  const openLink = () => {
    setHref(editor.getAttributes('link').href || '');
    setLinking(true);
  };

  const closeLink = () => {
    setLinking(false);
    editor.commands.focus();
  };

  const applyLink = (event) => {
    event?.preventDefault();
    const address = href.trim();
    const chain = editor.chain().focus().extendMarkRange('link');
    if (address) chain.setLink({ href: address }).run();
    else chain.unsetLink().run();
    // Leave the caret after the words, so typing carries on rather than
    // replacing what was just linked.
    editor.commands.setTextSelection(editor.state.selection.to);
    setLinking(false);
  };

  const groups = [
    [
      { label: 'Heading', icon: TextHTwo, active: state.h2, onClick: run((c) => c.toggleHeading({ level: 2 })) },
      { label: 'Subheading', icon: TextHThree, active: state.h3, onClick: run((c) => c.toggleHeading({ level: 3 })) },
    ],
    [
      { label: 'Bold', icon: TextB, active: state.bold, onClick: run((c) => c.toggleBold()) },
      { label: 'Italic', icon: TextItalic, active: state.italic, onClick: run((c) => c.toggleItalic()) },
      { label: 'Underline', icon: TextUnderline, active: state.underline, onClick: run((c) => c.toggleUnderline()) },
      { label: 'Strikethrough', icon: TextStrikethrough, active: state.strike, onClick: run((c) => c.toggleStrike()) },
    ],
    [
      { label: 'Bulleted list', icon: ListBullets, active: state.bulletList, onClick: run((c) => c.toggleBulletList()) },
      { label: 'Numbered list', icon: ListNumbers, active: state.orderedList, onClick: run((c) => c.toggleOrderedList()) },
      { label: 'Quote', icon: Quotes, active: state.blockquote, onClick: run((c) => c.toggleBlockquote()) },
    ],
    [
      { label: state.link ? 'Edit link' : 'Add link', icon: LinkSimple, active: state.link || linking, onClick: openLink },
      { label: 'Add image', icon: ImageIcon, onClick: () => setPickingImage(true) },
      { label: 'Dividing line', icon: Minus, onClick: run((c) => c.setHorizontalRule()) },
    ],
    [
      { label: 'Undo', icon: ArrowCounterClockwise, disabled: !state.canUndo, onClick: run((c) => c.undo()) },
      { label: 'Redo', icon: ArrowClockwise, disabled: !state.canRedo, onClick: run((c) => c.redo()) },
    ],
  ];

  return (
    <div className="rich-text-shell">
      <div className="rich-text-bar" role="toolbar" aria-label="Formatting">
        {groups.map((group, index) => (
          <div key={index} className="rich-text-group">
            {group.map(({ label, icon: Icon, active = false, disabled = false, onClick }) => (
              <button
                key={label}
                type="button"
                title={label}
                aria-label={label}
                aria-pressed={active}
                disabled={disabled}
                // Keep the selection in the text while a button is pressed.
                onMouseDown={(event) => event.preventDefault()}
                onClick={onClick}
                className="rich-text-button"
              >
                <Icon className="h-[18px] w-[18px]" />
              </button>
            ))}
          </div>
        ))}
      </div>

      {linking && (
        <form onSubmit={applyLink} className="rich-text-link">
          <label htmlFor="rich-text-link-address" className="text-[13px] font-medium text-[var(--color-ink-2)]">Link to</label>
          <input
            id="rich-text-link-address"
            ref={linkInput}
            className="field-control min-h-9 flex-1"
            value={href}
            onChange={(event) => setHref(event.target.value)}
            onKeyDown={(event) => {
              // Handled here, with the key press cancelled: left to submit the
              // form, the same Enter would land in the post once focus moves
              // back to it and replace the words being linked.
              if (event.key === 'Enter') applyLink(event);
              if (event.key === 'Escape') closeLink();
            }}
            placeholder="https://example.com or /contact/"
          />
          <button type="submit" className="rich-text-link-action font-medium text-[var(--color-ink)]">{href.trim() ? 'Apply' : 'Remove link'}</button>
          <button type="button" className="rich-text-link-action text-[var(--color-muted)]" onClick={closeLink}>Cancel</button>
        </form>
      )}

      <EditorContent editor={editor} />

      <MediaPicker
        isOpen={pickingImage}
        onClose={() => setPickingImage(false)}
        onSelect={(url, alt) => {
          editor.chain().focus().setImage({ src: url, alt: alt || '' }).run();
          setPickingImage(false);
        }}
      />
    </div>
  );
}
