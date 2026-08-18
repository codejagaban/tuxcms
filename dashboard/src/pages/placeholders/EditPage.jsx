import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';

const EditPage = () => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Edit Page</h1>
        <p className="text-gray-600 mt-1">Update page information</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Edit Page</CardTitle>
        </CardHeader>
        <CardContent className="py-12">
          <div className="text-center text-gray-500">
            <p>Page editor coming soon...</p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default EditPage;
