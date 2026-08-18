import { ChevronLeft, ChevronRight } from 'lucide-react';
import Button from './Button';

const Table = ({ className = '', children, ...props }) => {
  return (
    <div className="overflow-x-auto">
      <table
        className={`w-full text-sm text-left text-gray-600 ${className}`}
        {...props}
      >
        {children}
      </table>
    </div>
  );
};

const TableHeader = ({ className = '', children, ...props }) => {
  return (
    <thead
      className={`text-xs font-semibold text-gray-700 bg-gray-50 border-b border-gray-200 ${className}`}
      {...props}
    >
      {children}
    </thead>
  );
};

const TableBody = ({ className = '', children, ...props }) => {
  return (
    <tbody className={className} {...props}>
      {children}
    </tbody>
  );
};

const TableRow = ({ className = '', children, ...props }) => {
  return (
    <tr
      className={`border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150 ${className}`}
      {...props}
    >
      {children}
    </tr>
  );
};

const TableCell = ({ className = '', children, isHeader = false, ...props }) => {
  const Tag = isHeader ? 'th' : 'td';
  return (
    <Tag className={`px-6 py-4 ${className}`} {...props}>
      {children}
    </Tag>
  );
};

const TableHeadCell = ({ className = '', children, ...props }) => {
  return (
    <TableCell isHeader className={className} {...props}>
      {children}
    </TableCell>
  );
};

const Pagination = ({
  currentPage = 1,
  totalPages = 1,
  onPageChange,
  className = '',
}) => {
  return (
    <div
      className={`flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-white ${className}`}
    >
      <div className="text-sm text-gray-600">
        Page {currentPage} of {totalPages}
      </div>
      <div className="flex gap-2">
        <Button
          variant="ghost"
          size="sm"
          disabled={currentPage === 1}
          onClick={() => onPageChange(currentPage - 1)}
        >
          <ChevronLeft className="h-4 w-4" />
          Previous
        </Button>
        <Button
          variant="ghost"
          size="sm"
          disabled={currentPage === totalPages}
          onClick={() => onPageChange(currentPage + 1)}
        >
          Next
          <ChevronRight className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
};

export {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableCell,
  TableHeadCell,
  Pagination,
};
export default Table;
