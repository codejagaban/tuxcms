import { useState, useEffect } from 'react';
import { Upload, Trash2, Copy, File, Image as ImageIcon, Music, Video } from 'lucide-react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent } from '../../components/ui/Card';
import { mediaAPI } from '../../lib/api';

const Media = () => {
  const [media, setMedia] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isUploading, setIsUploading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, mediaId: null, mediaName: '' });
  const [isDeleting, setIsDeleting] = useState(false);
  const [detailsModal, setDetailsModal] = useState({ isOpen: false, media: null });
  const fileInputRef = useState(null)[1];

  const itemsPerPage = 12;

  useEffect(() => {
      fetchMedia();
  }, [currentPage]);

  const fetchMedia = async () => {
    try {
      setIsLoading(true);
      const response = await mediaAPI.list({
        page: currentPage,
        per_page: itemsPerPage,
      });

      const data = response.data.data || [];
      setMedia(data);

      const total = response.data.meta?.total || data.length;
      setTotalPages(Math.ceil(total / itemsPerPage));
    } catch (error) {
      console.error('Error fetching media:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load media');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleFileSelect = async (e) => {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;

    for (const file of files) {
      try {
        setIsUploading(true);
        await mediaAPI.upload(file);
        toast.success(`${file.name} uploaded successfully`);
      } catch (error) {
        console.error('Error uploading file:', error);
        toast.error(`Failed to upload ${file.name}`);
      }
    }

    setIsUploading(false);
    setCurrentPage(1);
    fetchMedia();

    e.target.value = '';
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    e.currentTarget.classList.add('bg-blue-50', 'border-blue-400');
  };

  const handleDragLeave = (e) => {
    e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.currentTarget.classList.remove('bg-blue-50', 'border-blue-400');

    const files = Array.from(e.dataTransfer.files || []);
    if (files.length === 0) return;

    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.files = e.dataTransfer.items[0].getAsFile() ? e.dataTransfer.files : null;

    const event = new Event('change', { bubbles: true });
    Object.defineProperty(event, 'target', {
      writable: false,
      value: { files },
    });

    handleFileSelect(event);
  };

  const handleDeleteClick = (mediaItem) => {
    setDeleteModal({
      isOpen: true,
      mediaId: mediaItem.id,
      mediaName: mediaItem.filename,
    });
  };

  const handleConfirmDelete = async () => {
    try {
      setIsDeleting(true);
      await mediaAPI.delete(deleteModal.mediaId);
      toast.success('Media deleted successfully');
      setDeleteModal({ isOpen: false, mediaId: null, mediaName: '' });
      setCurrentPage(1);
      fetchMedia();
    } catch (error) {
      console.error('Error deleting media:', error);
      toast.error('Failed to delete media');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleCloseDeleteModal = () => {
    setDeleteModal({ isOpen: false, mediaId: null, mediaName: '' });
  };

  const copyToClipboard = (url) => {
    navigator.clipboard.writeText(url);
    toast.success('URL copied to clipboard');
  };

  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const getMediaIcon = (mimeType) => {
    if (mimeType.startsWith('image/')) return <ImageIcon className="h-6 w-6 text-blue-500" />;
    if (mimeType.startsWith('audio/')) return <Music className="h-6 w-6 text-purple-500" />;
    if (mimeType.startsWith('video/')) return <Video className="h-6 w-6 text-green-500" />;
    return <File className="h-6 w-6 text-gray-500" />;
  };

  const isImageMedia = (mimeType) => mimeType.startsWith('image/');

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Media</h1>
          <p className="text-gray-600 mt-1">Manage your media files and images</p>
        </div>
      </div>

      {/* Upload Area */}
      <Card>
        <CardContent className="pt-6">
          <label
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
            onDrop={handleDrop}
            className="cursor-pointer block"
          >
            <div className="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-400 transition-colors">
              <Upload className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <h3 className="font-semibold text-gray-900">Drag and drop files here</h3>
              <p className="text-gray-600 mt-2">or click to select files</p>
              <p className="text-xs text-gray-500 mt-1">PNG, JPG, GIF, PDF, and other formats</p>
            </div>
            <input
              type="file"
              multiple
              onChange={handleFileSelect}
              disabled={isUploading}
              className="hidden"
            />
          </label>
        </CardContent>
      </Card>

      {isLoading && (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      )}

      {!isLoading && media.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  🖼️
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">No media files</h3>
              <p className="text-gray-600 mt-2">
                Upload your first file using the area above
              </p>
            </div>
          </CardContent>
        </Card>
      )}

      {!isLoading && media.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {media.map((item) => (
            <MediaCard
              key={item.id}
              media={item}
              isImageMedia={isImageMedia(item.mime_type)}
              onDelete={() => handleDeleteClick(item)}
              onViewDetails={() => setDetailsModal({ isOpen: true, media: item })}
              onCopyUrl={() => copyToClipboard(item.url)}
              getMediaIcon={getMediaIcon}
            />
          ))}
        </div>
      )}

      <MediaDetailsModal
        isOpen={detailsModal.isOpen}
        media={detailsModal.media}
        onClose={() => setDetailsModal({ isOpen: false, media: null })}
        onCopyUrl={() => copyToClipboard(detailsModal.media?.url)}
        onDelete={() => {
          handleDeleteClick(detailsModal.media);
          setDetailsModal({ isOpen: false, media: null });
        }}
        formatFileSize={formatFileSize}
        formatDate={formatDate}
      />

      <Modal
        isOpen={deleteModal.isOpen}
        onClose={handleCloseDeleteModal}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={handleCloseDeleteModal}>
          <ModalTitle>Delete Media</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-gray-600">
            Are you sure you want to delete <strong>{deleteModal.mediaName}</strong>? This action cannot be undone.
          </p>
        </ModalContent>
        <ModalFooter>
          <Button variant="secondary" onClick={handleCloseDeleteModal} disabled={isDeleting}>
            Cancel
          </Button>
          <Button variant="danger" onClick={handleConfirmDelete} isLoading={isDeleting}>
            Delete
          </Button>
        </ModalFooter>
      </Modal>
    </div>
  );
};

const MediaCard = ({ media, isImageMedia, onDelete, onViewDetails, onCopyUrl, getMediaIcon }) => {
  return (
    <div className="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
      {isImageMedia ? (
        <div className="aspect-square bg-gray-100 overflow-hidden">
          <img
            src={media.url}
            alt={media.file_name}
            className="w-full h-full object-cover hover:scale-110 transition-transform cursor-pointer"
            onClick={onViewDetails}
          />
        </div>
      ) : (
        <div
          className="aspect-square bg-gray-100 flex items-center justify-center cursor-pointer hover:bg-gray-200 transition-colors"
          onClick={onViewDetails}
        >
          {getMediaIcon(media.mime_type)}
        </div>
      )}

      <div className="p-4 space-y-3">
        <div>
          <p className="font-medium text-gray-900 truncate">{media.file_name}</p>
          <p className="text-xs text-gray-500 mt-1">{media.mime_type}</p>
        </div>

        <div className="flex items-center gap-2">
          <Button
            variant="ghost"
            size="sm"
            onClick={onCopyUrl}
            className="flex-1"
          >
            <Copy className="h-4 w-4" />
            Copy URL
          </Button>
          <Button
            variant="danger"
            size="sm"
            onClick={onDelete}
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
};

const MediaDetailsModal = ({ isOpen, media, onClose, onCopyUrl, onDelete, formatFileSize, formatDate }) => {
  if (!isOpen || !media) return null;

  const isImageMedia = media.mime_type.startsWith('image/');

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-2xl">
      <ModalHeader onClose={onClose}>
        <ModalTitle>Media Details</ModalTitle>
      </ModalHeader>

      <ModalContent className="space-y-4">
        {isImageMedia && (
          <div className="bg-gray-100 rounded-lg p-4 aspect-video flex items-center justify-center">
            <img
              src={media.url}
              alt={media.file_name}
              className="max-w-full max-h-full"
            />
          </div>
        )}

        <div className="space-y-3 border-t pt-4">
          <div>
            <p className="text-xs font-medium text-gray-600 uppercase">Filename</p>
            <p className="text-sm text-gray-900 mt-1 break-words">{media.file_name}</p>
          </div>

          <div>
            <p className="text-xs font-medium text-gray-600 uppercase">URL</p>
            <div className="flex items-center gap-2 mt-1">
              <input
                type="text"
                value={media.url}
                readOnly
                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50"
              />
              <Button variant="ghost" size="sm" onClick={onCopyUrl}>
                <Copy className="h-4 w-4" />
              </Button>
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
            <div>
              <p className="text-xs font-medium text-gray-600 uppercase">Size</p>
              <p className="text-sm text-gray-900 mt-1">{formatFileSize(media.size)}</p>
            </div>
            <div>
              <p className="text-xs font-medium text-gray-600 uppercase">Type</p>
              <p className="text-sm text-gray-900 mt-1">{media.mime_type}</p>
            </div>
            <div>
              <p className="text-xs font-medium text-gray-600 uppercase">Uploaded</p>
              <p className="text-sm text-gray-900 mt-1">{formatDate(media.created_at)}</p>
            </div>
          </div>
        </div>
      </ModalContent>

      <ModalFooter>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
        <Button variant="danger" onClick={onDelete}>
          <Trash2 className="h-4 w-4" />
          Delete
        </Button>
      </ModalFooter>
    </Modal>
  );
};

export default Media;
