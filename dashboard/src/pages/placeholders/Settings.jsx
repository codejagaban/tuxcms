import { useState, useEffect } from 'react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Spinner from '../../components/ui/Spinner';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { settingsAPI } from '../../lib/api';

const Settings = () => {
  const [settings, setSettings] = useState({
    site_name: '',
    site_description: '',
    site_url: '',
    meta_keywords: '',
    google_analytics_id: '',
    google_site_verification: '',
    social_facebook: '',
    social_twitter: '',
    social_instagram: '',
    social_linkedin: '',
    contact_email: '',
    notification_email: '',
  });

  const [isLoading, setIsLoading] = useState(true);
  const [savingGroups, setSavingGroups] = useState({});

  useEffect(() => {
      fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setIsLoading(true);
      const response = await settingsAPI.get();
      const data = response.data.data || {};

      setSettings({
        site_name: data.site_name || '',
        site_description: data.site_description || '',
        site_url: data.site_url || '',
        meta_keywords: data.meta_keywords || '',
        google_analytics_id: data.google_analytics_id || '',
        google_site_verification: data.google_site_verification || '',
        social_facebook: data.social_facebook || '',
        social_twitter: data.social_twitter || '',
        social_instagram: data.social_instagram || '',
        social_linkedin: data.social_linkedin || '',
        contact_email: data.contact_email || '',
        notification_email: data.notification_email || '',
      });
    } catch (error) {
      console.error('Error fetching settings:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load settings');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setSettings((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const saveGroup = async (groupName, groupSettings) => {
    try {
      setSavingGroups((prev) => ({
        ...prev,
        [groupName]: true,
      }));

      const dataToSave = {};
      groupSettings.forEach((key) => {
        dataToSave[key] = settings[key];
      });

      await settingsAPI.update(dataToSave);
      toast.success(`${groupName} settings updated successfully`);
    } catch (error) {
      console.error('Error saving settings:', error);
      toast.error(`Failed to save ${groupName} settings`);
    } finally {
      setSavingGroups((prev) => ({
        ...prev,
        [groupName]: false,
      }));
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Settings</h1>
        <p className="text-gray-600 mt-1">Manage your site settings and configuration</p>
      </div>

      {/* Site Settings */}
      <SettingsGroup
        title="Site Settings"
        description="Basic site information"
        fields={[
          { key: 'site_name', label: 'Site Name', placeholder: 'My Awesome Site' },
          { key: 'site_description', label: 'Site Description', placeholder: 'Your site description' },
          { key: 'site_url', label: 'Site URL', placeholder: 'https://example.com' },
        ]}
        settings={settings}
        onChange={handleInputChange}
        onSave={() => saveGroup('Site', ['site_name', 'site_description', 'site_url'])}
        isSaving={savingGroups.Site}
      />

      {/* SEO Settings */}
      <SettingsGroup
        title="SEO Settings"
        description="Search engine optimization settings"
        fields={[
          { key: 'meta_keywords', label: 'Meta Keywords', placeholder: 'keyword1, keyword2, keyword3' },
          { key: 'google_analytics_id', label: 'Google Analytics ID', placeholder: 'UA-123456789-0' },
          { key: 'google_site_verification', label: 'Google Site Verification', placeholder: 'verification code' },
        ]}
        settings={settings}
        onChange={handleInputChange}
        onSave={() => saveGroup('SEO', ['meta_keywords', 'google_analytics_id', 'google_site_verification'])}
        isSaving={savingGroups.SEO}
      />

      {/* Social Settings */}
      <SettingsGroup
        title="Social Media Settings"
        description="Links to your social media profiles"
        fields={[
          { key: 'social_facebook', label: 'Facebook URL', placeholder: 'https://facebook.com/yourpage' },
          { key: 'social_twitter', label: 'Twitter URL', placeholder: 'https://twitter.com/yourhandle' },
          { key: 'social_instagram', label: 'Instagram URL', placeholder: 'https://instagram.com/yourhandle' },
          { key: 'social_linkedin', label: 'LinkedIn URL', placeholder: 'https://linkedin.com/in/yourprofile' },
        ]}
        settings={settings}
        onChange={handleInputChange}
        onSave={() => saveGroup('Social', ['social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin'])}
        isSaving={savingGroups.Social}
      />

      {/* Email Settings */}
      <SettingsGroup
        title="Email Settings"
        description="Contact and notification email addresses"
        fields={[
          { key: 'contact_email', label: 'Contact Email', placeholder: 'contact@example.com', type: 'email' },
          { key: 'notification_email', label: 'Notification Email', placeholder: 'notifications@example.com', type: 'email' },
        ]}
        settings={settings}
        onChange={handleInputChange}
        onSave={() => saveGroup('Email', ['contact_email', 'notification_email'])}
        isSaving={savingGroups.Email}
      />
    </div>
  );
};

const SettingsGroup = ({ title, description, fields, settings, onChange, onSave, isSaving }) => {
  return (
    <Card>
      <CardHeader>
        <div className="flex items-start justify-between">
          <div>
            <CardTitle>{title}</CardTitle>
            <p className="text-sm text-gray-600 mt-1">{description}</p>
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-4">
        {fields.map((field) => (
          <Input
            key={field.key}
            label={field.label}
            placeholder={field.placeholder}
            name={field.key}
            type={field.type || 'text'}
            value={settings[field.key] || ''}
            onChange={onChange}
            containerClassName="w-full"
          />
        ))}

        <div className="flex items-center justify-end pt-4 border-t">
          <Button variant="primary" onClick={onSave} isLoading={isSaving}>
            Save Changes
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

export default Settings;
