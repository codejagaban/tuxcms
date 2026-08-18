import { useState, useEffect } from 'react';
import { FileText, FormInput, MessageSquare, Image, Building2 } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { pageAPI, formAPI, mediaAPI, tenantAPI } from '../lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/Card';
import Spinner from '../components/ui/Spinner';

const Dashboard = () => {
  const { user, currentTenant, isSuperAdmin } = useAuth();
  const [stats, setStats] = useState({
    totalPages: 0,
    totalForms: 0,
    formSubmissions: 0,
    mediaFiles: 0,
    totalTenants: 0,
  });
  const [recentPages, setRecentPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setIsLoading(true);

        // Fetch pages
        try {
          const pagesResponse = await pageAPI.list({ per_page: 100 });
          const pages = pagesResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            totalPages: pagesResponse.data.meta?.total || pages.length,
          }));
          setRecentPages(pages.slice(0, 5));
        } catch (error) {
          // Pages endpoint may fail without tenant context
        }

        // Fetch forms
        try {
          const formsResponse = await formAPI.list({ per_page: 100 });
          const forms = formsResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            totalForms: formsResponse.data.meta?.total || forms.length,
          }));

          // Calculate form submissions
          let totalSubmissions = 0;
          for (const form of forms.slice(0, 10)) {
            try {
              const submissionsResponse = await formAPI.getSubmissions(form.id);
              totalSubmissions +=
                submissionsResponse.data.meta?.total ||
                submissionsResponse.data.data?.length || 0;
            } catch (error) {
              // Continue if error
            }
          }
          setStats((prev) => ({
            ...prev,
            formSubmissions: totalSubmissions,
          }));
        } catch (error) {
          // Forms endpoint may fail without tenant context
        }

        // Fetch media
        try {
          const mediaResponse = await mediaAPI.list({ per_page: 100 });
          const mediaFiles = mediaResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            mediaFiles: mediaResponse.data.meta?.total || mediaFiles.length,
          }));
        } catch (error) {
          // Media endpoint may not be fully set up yet
        }

        // Fetch tenant count (super admin only)
        if (isSuperAdmin) {
          try {
            const tenantsResponse = await tenantAPI.list({ per_page: 1 });
            setStats((prev) => ({
              ...prev,
              totalTenants: tenantsResponse.data.meta?.total || 0,
            }));
          } catch (error) {
            // Tenants endpoint may not be accessible
          }
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        setIsLoading(false);
      }
    };

    // Fetch data if tenant is selected, or if super admin (backend resolves tenant)
    if (currentTenant || isSuperAdmin) {
      fetchData();
    } else {
      setIsLoading(false);
    }
  }, [currentTenant, isSuperAdmin]);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!currentTenant && !isSuperAdmin) {
    return (
      <div className="space-y-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">
            Welcome back, {user?.name?.split(' ')[0]}!
          </h1>
          <p className="text-gray-600 mt-2">
            No tenant selected. Please select or create a tenant to get started.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      {/* Welcome Section */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900">
          Welcome back, {user?.name?.split(' ')[0]}!
        </h1>
        <p className="text-gray-600 mt-2">
          Here's what's happening in {currentTenant?.name || 'your dashboard'} today.
        </p>
      </div>

      {/* Stats Grid */}
      <div className={`grid grid-cols-1 md:grid-cols-2 ${isSuperAdmin ? 'lg:grid-cols-5' : 'lg:grid-cols-4'} gap-6`}>
        {/* Total Pages */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Pages</p>
                <p className="text-3xl font-bold text-gray-900 mt-2">
                  {stats.totalPages}
                </p>
              </div>
              <div className="p-3 bg-blue-100 rounded-lg">
                <FileText className="h-6 w-6 text-blue-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Total Forms */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Forms</p>
                <p className="text-3xl font-bold text-gray-900 mt-2">
                  {stats.totalForms}
                </p>
              </div>
              <div className="p-3 bg-green-100 rounded-lg">
                <FormInput className="h-6 w-6 text-green-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Form Submissions */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">
                  Form Submissions
                </p>
                <p className="text-3xl font-bold text-gray-900 mt-2">
                  {stats.formSubmissions}
                </p>
              </div>
              <div className="p-3 bg-purple-100 rounded-lg">
                <MessageSquare className="h-6 w-6 text-purple-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Media Files */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">
                  Media Files
                </p>
                <p className="text-3xl font-bold text-gray-900 mt-2">
                  {stats.mediaFiles}
                </p>
              </div>
              <div className="p-3 bg-yellow-100 rounded-lg">
                <Image className="h-6 w-6 text-yellow-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Total Tenants (super admin only) */}
        {isSuperAdmin && (
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">
                    Total Tenants
                  </p>
                  <p className="text-3xl font-bold text-gray-900 mt-2">
                    {stats.totalTenants}
                  </p>
                </div>
                <div className="p-3 bg-indigo-100 rounded-lg">
                  <Building2 className="h-6 w-6 text-indigo-600" />
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Recent Pages */}
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
            <div className="space-y-3">
              {recentPages.map((page) => (
                <div
                  key={page.id}
                  className="flex items-center justify-between p-4 hover:bg-gray-50 rounded-lg transition-colors"
                >
                  <div>
                    <h3 className="font-medium text-gray-900">
                      {page.title}
                    </h3>
                    <p className="text-sm text-gray-500 mt-1">
                      {page.slug}
                    </p>
                  </div>
                  <div className="text-right">
                    <div className="text-sm font-medium text-gray-900">
                      {page.status || 'draft'}
                    </div>
                    <p className="text-xs text-gray-500">
                      {new Date(page.updated_at).toLocaleDateString()}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
