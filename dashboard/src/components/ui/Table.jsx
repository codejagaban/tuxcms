import { CaretLeft, CaretRight } from '@phosphor-icons/react';
import Button from './Button';

const Table = ({ className = '', children, ...props }) => {
  return (
    <div className="overflow-x-auto">
      <table
        className={`w-full text-left text-sm text-[var(--color-neutral)] ${className}`}
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
      className={`border-b border-[var(--color-rule-2)] bg-[var(--color-paper-2)] text-xs font-semibold text-[var(--color-ink-2)] ${className}`}
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
      className={`border-b border-[var(--color-rule-2)] transition-colors duration-150 hover:bg-[var(--color-paper-2)] ${className}`}
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
      className={`flex items-center justify-between border-t border-[var(--color-rule-2)] bg-[var(--color-paper)] px-6 py-4 ${className}`}
    >
      <div className="text-sm text-[var(--color-muted)]">
        Page {currentPage} of {totalPages}
      </div>
      <div className="flex gap-2">
        <Button
          variant="ghost"
          size="sm"
          disabled={currentPage === 1}
          onClick={() => onPageChange(currentPage - 1)}
        >
          <CaretLeft className="h-4 w-4" weight="bold" />
          Previous
        </Button>
        <Button
          variant="ghost"
          size="sm"
          disabled={currentPage === totalPages}
          onClick={() => onPageChange(currentPage + 1)}
        >
          Next
          <CaretRight className="h-4 w-4" weight="bold" />
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
