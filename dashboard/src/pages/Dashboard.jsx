import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { FileText, Image, FileCheck, PenLine } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { pageAPI, mediaAPI } from '../lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/Card';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';

const Dashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState({
    totalPages: 0,
    publishedPages: 0,
    draftPages: 0,
    mediaFiles: 0,
  });
  const [recentPages, setRecentPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setIsLoading(true);

        try {
          const pagesResponse = await pageAPI.list({ per_page: 100 });
          const pages = pagesResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            totalPages: pagesResponse.data.meta?.total || pages.length,
            publishedPages: pages.filter((p) => p.status === 'published').length,
            draftPages: pages.filter((p) => p.status === 'draft').length,
          }));
          setRecentPages(pages.slice(0, 5));
        } catch (error) {
          console.error('Error fetching pages:', error);
        }

        try {
          const mediaResponse = await mediaAPI.list({ per_page: 100 });
          const mediaFiles = mediaResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            mediaFiles: mediaResponse.data.meta?.total || mediaFiles.length,
          }));
        } catch (error) {
          console.error('Error fetching media:', error);
        }
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  const cards = [
    { label: 'Total Pages', value: stats.totalPages, icon: FileText, tone: 'bg-blue-100 text-blue-600' },
    { label: 'Published', value: stats.publishedPages, icon: FileCheck, tone: 'bg-green-100 text-green-600' },
    { label: 'Drafts', value: stats.draftPages, icon: PenLine, tone: 'bg-amber-100 text-amber-600' },
    { label: 'Media Files', value: stats.mediaFiles, icon: Image, tone: 'bg-purple-100 text-purple-600' },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">
          Welcome back, {user?.name?.split(' ')[0]}!
        </h1>
        <p className="text-gray-600 mt-2">Here's the state of your website today.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {cards.map(({ label, value, icon: Icon, tone }) => (
          <Card key={label}>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">{label}</p>
                  <p className="text-3xl font-bold text-gray-900 mt-2">{value}</p>
                </div>
                <div className={`p-3 rounded-lg ${tone}`}>
                  <Icon className="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Recent Pages</CardTitle>
        </CardHeader>
        <CardContent>
          {recentPages.length === 0 ? (
            <p className="text-gray-600 py-8 text-center">
              No pages yet. Create your first page to get started.
            </p>
          ) : (
            <div className="space-y-1">
              {recentPages.map((page) => (
                <Link
                  key={page.id}
                  to={`/dashboard/pages/${page.id}/edit`}
                  className="flex items-center justify-between p-4 hover:bg-gray-50 rounded-lg transition-colors"
                >
                  <div>
                    <h3 className="font-medium text-gray-900">{page.title}</h3>
                    <p className="text-sm text-gray-500 mt-1">{page.full_path || `/${page.slug}`}</p>
                  </div>
                  <div className="flex items-center gap-4">
                    <Badge variant={page.status === 'published' ? 'published' : 'draft'}>
                      {page.status || 'draft'}
                    </Badge>
                    <p className="text-xs text-gray-500 w-20 text-right">
                      {new Date(page.updated_at).toLocaleDateString()}
                    </p>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
