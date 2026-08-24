import { useState, useEffect, useCallback } from 'react';
import { Globe, Check, Loader2, AlertCircle } from 'lucide-react';
import toast from 'react-hot-toast';
import { siteAPI } from '../lib/api';

/**
 * Publishes the site by regenerating the static HTML.
 *
 * Shows real state — pending change count, last publish time, failures — so
 * an editor can tell whether what they see in the dashboard is actually live.
 */
export default function PublishButton() {
  const [status, setStatus] = useState(null);
  const [publishing, setPublishing] = useState(false);
  const [failed, setFailed] = useState(false);

  const refresh = useCallback(async () => {
    try {
      const res = await siteAPI.status();
      setStatus(res.data.data);
    } catch {
      // A status failure shouldn't block editing; the button still works.
    }
  }, []);

  useEffect(() => {
    refresh();
    const timer = setInterval(refresh, 30000);

    // Saving a published page republishes automatically; pick that up rather
    // than waiting up to 30s to look stale.
    window.addEventListener('tuxcms:published', refresh);

    return () => {
      clearInterval(timer);
      window.removeEventListener('tuxcms:published', refresh);
    };
  }, [refresh]);

  const publish = async () => {
    setPublishing(true);
    setFailed(false);
    try {
      const res = await siteAPI.build();
      const { pages } = res.data.data;
      toast.success(`Published ${pages} ${pages === 1 ? 'page' : 'pages'}`);
      await refresh();
    } catch (error) {
      setFailed(true);
      toast.error(error.response?.data?.message || 'Publish failed');
    } finally {
      setPublishing(false);
    }
  };

  const pending = status?.pending_changes ?? 0;
  const lastPublished = status?.last_published_at
    ? new Date(status.last_published_at)
    : null;

  const relativeTime = (date) => {
    const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    return date.toLocaleDateString();
  };

  let label = 'Publish';
  let detail = null;

  if (publishing) {
    label = 'Publishing…';
  } else if (failed) {
    detail = 'Last publish failed';
  } else if (pending > 0) {
    detail = `${pending} ${pending === 1 ? 'change' : 'changes'} pending`;
  } else if (lastPublished) {
    detail = `Published ${relativeTime(lastPublished)}`;
  } else {
    detail = 'Not published yet';
  }

  return (
    <div className="flex items-center gap-3">
      <div className="hidden text-right sm:block">
        <div className="flex items-center justify-end gap-1.5 text-xs text-gray-500">
          {failed ? (
            <AlertCircle className="h-3.5 w-3.5 text-red-500" />
          ) : pending > 0 ? (
            <span className="h-2 w-2 rounded-full bg-amber-400" />
          ) : lastPublished ? (
            <Check className="h-3.5 w-3.5 text-green-600" />
          ) : null}
          {detail}
        </div>
      </div>

      <button
        onClick={publish}
        disabled={publishing}
        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
      >
        {publishing ? (
          <Loader2 className="h-4 w-4 animate-spin" />
        ) : (
          <Globe className="h-4 w-4" />
        )}
        {label}
      </button>
    </div>
  );
}
