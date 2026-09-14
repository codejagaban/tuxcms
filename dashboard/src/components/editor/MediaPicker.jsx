import { useEffect, useRef, useState } from 'react';
import { ImageSquare, UploadSimple } from '@phosphor-icons/react';
import toast from 'react-hot-toast';
import { apiErrorMessage, mediaAPI } from '../../lib/api';
import Button from '../ui/Button';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../ui/Modal';
import Spinner from '../ui/Spinner';

export default function MediaPicker({ isOpen, currentUrl = '', currentAlt = '', onClose, onSelect }) {
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [selected, setSelected] = useState(null);
  const [altText, setAltText] = useState(currentAlt);
  const inputRef = useRef(null);

  const loadImages = async () => {
    setLoading(true);
    try {
      const response = await mediaAPI.list({ per_page: 100 });
      setImages((response.data.data || []).filter((item) => item.mime_type?.startsWith('image/')));
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load the media library'));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!isOpen) return;
    setSelected(currentUrl ? { url: currentUrl, file_name: 'Current image' } : null);
    setAltText(currentAlt || '');
    loadImages();
  }, [isOpen, currentUrl, currentAlt]);

  const upload = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;

    setUploading(true);
    try {
      const response = await mediaAPI.upload(file, { altText: altText || file.name.replace(/\.[^.]+$/, '') });
      const item = response.data.data;
      setImages((existing) => [item, ...existing.filter((image) => image.id !== item.id)]);
      setSelected(item);
      if (!altText) setAltText(file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' '));
      toast.success('Image uploaded');
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Image upload failed'));
    } finally {
      setUploading(false);
    }
  };

  const availableImages = currentUrl && !images.some((image) => image.url === currentUrl)
    ? [{ id: `current:${currentUrl}`, url: currentUrl, file_name: 'Current image', name: currentAlt }, ...images]
    : images;

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-4xl">
      <ModalHeader onClose={onClose}>
        <div>
          <ModalTitle>Choose image</ModalTitle>
          <p className="mt-1 text-sm text-[var(--color-muted)]">Upload a new image or reuse one from the library.</p>
        </div>
      </ModalHeader>

      <ModalContent className="space-y-5">
        <div className="flex flex-wrap items-end gap-3">
          <label className="min-w-0 flex-1">
            <span className="mb-1 block text-xs font-medium text-gray-600">Alternative text</span>
            <input
              className="field-control text-sm"
              value={altText}
              onChange={(event) => setAltText(event.target.value)}
              placeholder="Describe the image for screen readers"
            />
          </label>
          <input ref={inputRef} type="file" accept="image/*" className="hidden" onChange={upload} />
          <Button variant="secondary" onClick={() => inputRef.current?.click()} disabled={uploading}>
            <UploadSimple className="h-4 w-4" weight="bold" />
            {uploading ? 'Uploading…' : 'Upload image'}
          </Button>
        </div>

        {loading ? (
          <div className="flex min-h-48 items-center justify-center"><Spinner /></div>
        ) : availableImages.length ? (
          <div className="grid max-h-[52dvh] grid-cols-2 gap-3 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-4">
            {availableImages.map((image) => {
              const active = selected?.url === image.url;
              return (
                <button
                  key={image.id}
                  type="button"
                  onClick={() => {
                    setSelected(image);
                    if (!altText) setAltText(image.custom_properties?.alt_text || image.name || '');
                  }}
                  className={`overflow-hidden rounded-md bg-[var(--color-paper-2)] text-left ${active ? 'ring-2 ring-black ring-offset-2' : 'ring-1 ring-[var(--color-rule-2)]'}`}
                >
                  <img src={image.url} alt="" className="aspect-[4/3] w-full object-cover" />
                  <span className="block truncate px-2.5 py-2 text-xs text-[var(--color-ink)]">{image.file_name}</span>
                </button>
              );
            })}
          </div>
        ) : (
          <button
            type="button"
            onClick={() => inputRef.current?.click()}
            className="flex min-h-48 w-full flex-col items-center justify-center rounded-md border border-dashed border-[var(--color-rule)] text-[var(--color-muted)]"
          >
            <ImageSquare className="mb-3 h-8 w-8" />
            <span className="font-medium text-[var(--color-ink)]">No library images yet</span>
            <span className="mt-1 text-sm">Upload the first image</span>
          </button>
        )}
      </ModalContent>

      <ModalFooter>
        <Button variant="secondary" onClick={onClose}>Cancel</Button>
        <Button variant="primary" disabled={!selected?.url || uploading} onClick={() => onSelect(selected.url, altText)}>
          Use image
        </Button>
      </ModalFooter>
    </Modal>
  );
}
