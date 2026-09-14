import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Spinner from '../../components/ui/Spinner';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../../components/ui/Card';
import { pageAPI, seoAPI } from '../../lib/api';

const SEO = () => {
  const [pages, setPages] = useState([]);
  const [selectedPageId, setSelectedPageId] = useState(null);
  const [selectedPage, setSelectedPage] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [seoData, setSeoData] = useState({
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    og_title: '',
    og_description: '',
    og_image: '',
    twitter_card: 'summary',
    twitter_title: '',
    twitter_description: '',
    canonical_url: '',
    robots: 'index, follow',
    schema_markup: '{}',
  });

  useEffect(() => {
      fetchPages();
    // The initial load deliberately runs once; selection changes load directly.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const fetchPages = async () => {
    try {
      setIsLoading(true);
      const response = await pageAPI.list({ per_page: 100 });
      const pagesData = response.data.data || [];
      setPages(pagesData);

      if (pagesData.length > 0) {
        const firstPage = pagesData[0];
        setSelectedPageId(firstPage.id);
        setSelectedPage(firstPage);
        fetchPageSEO(firstPage.id, firstPage);
      }
    } catch (error) {
      console.error('Error fetching pages:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load pages');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const fetchPageSEO = async (pageId, knownPage = null) => {
    try {
      const page = knownPage || pages.find((p) => p.id === pageId) || selectedPage;
      setSelectedPage(page);

      try {
        const response = await seoAPI.get(pageId);
        const data = response.data.data || {};

        setSeoData({
          ...data,
          og_image: data.og_image || '',
          schema_markup: JSON.stringify(data.schema_markup || {}, null, 2),
        });
      } catch {
        // If SEO data doesn't exist, use defaults
        setSeoData({
          meta_title: page?.title || '',
          meta_description: page?.excerpt || '',
          meta_keywords: '',
          og_title: page?.title || '',
          og_description: page?.excerpt || '',
          og_image: '',
          twitter_card: 'summary',
          twitter_title: page?.title || '',
          twitter_description: page?.excerpt || '',
          canonical_url: '',
          robots: 'index, follow',
          schema_markup: '{}',
        });
      }
    } catch (error) {
      console.error('Error fetching page SEO:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load SEO data');
      }
    }
  };

  const handlePageSelect = (pageId) => {
    setSelectedPageId(pageId);
    fetchPageSEO(pageId);
  };

  const handleSave = async () => {
    if (!selectedPageId) {
      toast.error('Please select a page');
      return;
    }

    try {
      setIsSaving(true);
      let schemaMarkup = null;

      try {
        schemaMarkup = seoData.schema_markup.trim()
          ? JSON.parse(seoData.schema_markup)
          : null;
      } catch {
        toast.error('Schema markup must be valid JSON');
        return;
      }

      await seoAPI.update(selectedPageId, {
        ...seoData,
        schema_markup: schemaMarkup,
      });
      toast.success('SEO data updated successfully');
    } catch (error) {
      console.error('Error saving SEO data:', error);
      toast.error('Failed to save SEO data');
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (pages.length === 0) {
    return (
      <div className="space-y-6">
        <div>
          <h1 className="page-heading">Search</h1>
          <p className="page-deck">
            Control how published pages appear in search and social previews.
          </p>
        </div>

        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
              </div>
              <h3 className="text-lg font-medium text-gray-900">No pages yet</h3>
              <p className="text-gray-600 mt-2">
                Create a page first to manage its SEO settings
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-heading">Search</h1>
        <p className="page-deck">
          Control how published pages appear in search and social previews.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Page Selector */}
        <div>
          <Card className="sticky top-6">
            <CardHeader>
              <CardTitle className="text-lg">Pages</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {pages.map((page) => (
                <button
                  key={page.id}
                  onClick={() => handlePageSelect(page.id)}
                  className={`w-full text-left px-3 py-2 rounded-lg transition-colors ${
                    selectedPageId === page.id
                      ? 'bg-neutral-100 text-black font-medium'
                      : 'hover:bg-gray-100 text-gray-700'
                  }`}
                >
                  {page.title}
                </button>
              ))}
            </CardContent>
          </Card>
        </div>

        {/* SEO Editor */}
        <div className="lg:col-span-2 space-y-6">
          {selectedPage && (
            <>
              {/* Basic Meta Tags */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Meta Tags</CardTitle>
                  <CardDescription>
                    These tags appear in search engine results
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <Input
                    label="Meta Title"
                    placeholder="SEO page title (50-60 characters)"
                    value={seoData.meta_title}
                    onChange={(e) =>
                      setSeoData({ ...seoData, meta_title: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  <div>
                    <label className="mb-2 text-sm font-medium text-gray-700 block">
                      Meta Description
                    </label>
                    <textarea
                      placeholder="Page description (150-160 characters)"
                      value={seoData.meta_description}
                      onChange={(e) =>
                        setSeoData({
                          ...seoData,
                          meta_description: e.target.value,
                        })
                      }
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                      rows="3"
                    />
                    <p className="text-xs text-gray-500 mt-1">
                      {seoData.meta_description.length} characters
                    </p>
                  </div>

                  <Input
                    label="Meta Keywords"
                    placeholder="keyword1, keyword2, keyword3"
                    value={seoData.meta_keywords}
                    onChange={(e) =>
                      setSeoData({ ...seoData, meta_keywords: e.target.value })
                    }
                    containerClassName="w-full"
                  />
                </CardContent>
              </Card>

              {/* Search Preview */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Google Search Preview</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="border border-gray-300 rounded-lg p-4 bg-gray-50">
                    <div className="text-black text-sm font-medium truncate">
                      {seoData.meta_title || selectedPage.title}
                    </div>
                    <div className="text-black text-xs mt-1">
                      example.com/{selectedPage.slug}
                    </div>
                    <div className="text-gray-700 text-sm mt-2 line-clamp-2">
                      {seoData.meta_description || selectedPage.excerpt}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Open Graph */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Open Graph (Social Media)</CardTitle>
                  <CardDescription>
                    Control how your page appears when shared on social media
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <Input
                    label="OG Title"
                    placeholder="Social media title"
                    value={seoData.og_title}
                    onChange={(e) =>
                      setSeoData({ ...seoData, og_title: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  <div>
                    <label className="mb-2 text-sm font-medium text-gray-700 block">
                      OG Description
                    </label>
                    <textarea
                      placeholder="Social media description"
                      value={seoData.og_description}
                      onChange={(e) =>
                        setSeoData({
                          ...seoData,
                          og_description: e.target.value,
                        })
                      }
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                      rows="3"
                    />
                  </div>

                  <Input
                    label="OG Image URL"
                    placeholder="https://example.com/image.jpg"
                    value={seoData.og_image}
                    onChange={(e) =>
                      setSeoData({ ...seoData, og_image: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  {seoData.og_image && (
                    <div className="mt-4">
                      <img
                        src={seoData.og_image}
                        alt="OG Preview"
                        className="w-full max-w-sm rounded-lg border border-gray-300"
                        onError={(e) => {
                          e.target.style.display = 'none';
                        }}
                      />
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* Social Preview */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Social Media Preview</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="border border-gray-300 rounded-lg overflow-hidden">
                    {seoData.og_image && (
                      <img
                        src={seoData.og_image}
                        alt="Preview"
                        className="w-full h-48 object-cover"
                        onError={(e) => {
                          e.target.style.display = 'none';
                        }}
                      />
                    )}
                    <div className="p-4 bg-gray-50">
                      <div className="font-medium text-gray-900 text-sm">
                        {seoData.og_title || selectedPage.title}
                      </div>
                      <div className="text-gray-600 text-xs mt-1">
                        {seoData.og_description || selectedPage.excerpt}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Twitter Card */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Twitter Card</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <label className="mb-2 text-sm font-medium text-gray-700 block">
                      Card Type
                    </label>
                    <select
                      value={seoData.twitter_card}
                      onChange={(e) =>
                        setSeoData({ ...seoData, twitter_card: e.target.value })
                      }
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                    >
                      <option value="summary">Summary</option>
                      <option value="summary_large_image">
                        Summary Large Image
                      </option>
                      <option value="app">App</option>
                      <option value="player">Player</option>
                    </select>
                  </div>

                  <Input
                    label="Twitter Title"
                    placeholder="Twitter title"
                    value={seoData.twitter_title}
                    onChange={(e) =>
                      setSeoData({ ...seoData, twitter_title: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  <div>
                    <label className="mb-2 text-sm font-medium text-gray-700 block">
                      Twitter Description
                    </label>
                    <textarea
                      placeholder="Twitter description"
                      value={seoData.twitter_description}
                      onChange={(e) =>
                        setSeoData({
                          ...seoData,
                          twitter_description: e.target.value,
                        })
                      }
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                      rows="3"
                    />
                  </div>
                </CardContent>
              </Card>

              {/* Advanced SEO */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg">Advanced SEO</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <Input
                    label="Canonical URL"
                    placeholder="https://example.com/page"
                    value={seoData.canonical_url}
                    onChange={(e) =>
                      setSeoData({ ...seoData, canonical_url: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  <Input
                    label="Robots"
                    placeholder="index, follow"
                    value={seoData.robots}
                    onChange={(e) =>
                      setSeoData({ ...seoData, robots: e.target.value })
                    }
                    containerClassName="w-full"
                  />

                  <div>
                    <label className="mb-2 text-sm font-medium text-gray-700 block">
                      Schema Markup (JSON-LD)
                    </label>
                    <textarea
                      placeholder='{"@context": "https://schema.org"}'
                      value={seoData.schema_markup}
                      onChange={(e) =>
                        setSeoData({ ...seoData, schema_markup: e.target.value })
                      }
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black font-mono text-sm"
                      rows="6"
                    />
                  </div>
                </CardContent>
              </Card>

              {/* Save Button */}
              <div className="flex gap-3">
                <Button
                  variant="primary"
                  onClick={handleSave}
                  isLoading={isSaving}
                  className="w-full"
                >
                  Save SEO Changes
                </Button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default SEO;
