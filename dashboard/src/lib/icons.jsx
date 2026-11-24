// The dashboard's icons: Lucide, drawn at one stroke weight. Components keep
// the names the screens already import.
import {
  ArrowLeft as LArrowLeft,
  ArrowUpRight as LArrowUpRight,
  Box as LBox,
  Briefcase as LBriefcase,
  Check as LCheck,
  ChevronDown as LChevronDown,
  ChevronLeft as LChevronLeft,
  ChevronRight as LChevronRight,
  ChevronUp as LChevronUp,
  CircleAlert as LCircleAlert,
  CircleCheck as LCircleCheck,
  CircleHelp as LCircleHelp,
  Clock as LClock,
  Code as LCode,
  Compass as LCompass,
  Copy as LCopy,
  Download as LDownload,
  Eye as LEye,
  EyeOff as LEyeOff,
  File as LFile,
  FileText as LFileText,
  Files as LFiles,
  Gauge as LGauge,
  Globe as LGlobe,
  History as LHistory,
  Image as LImage,
  Inbox as LInbox,
  Layers as LLayers,
  LayoutGrid as LLayoutGrid,
  LoaderCircle as LLoaderCircle,
  LogOut as LLogOut,
  Mail as LMail,
  MapPin as LMapPin,
  Menu as LMenu,
  MessageSquareText as LMessageSquareText,
  Monitor as LMonitor,
  Music as LMusic,
  PenTool as LPenTool,
  Pencil as LPencil,
  PencilLine as LPencilLine,
  Phone as LPhone,
  Plus as LPlus,
  RotateCcw as LRotateCcw,
  Search as LSearch,
  Settings as LSettings,
  Shield as LShield,
  Smartphone as LSmartphone,
  Sparkles as LSparkles,
  Star as LStar,
  Trash2 as LTrash2,
  Upload as LUpload,
  Users as LUsers,
  Video as LVideo,
  X as LX,
  Zap as LZap,
} from 'lucide-react';

// The old set took a `weight`; here every icon shares one line weight.
const icon = (Glyph) => {
  // eslint-disable-next-line no-unused-vars
  const Icon = ({ weight, ...props }) => <Glyph strokeWidth={1.75} aria-hidden="true" {...props} />;
  return Icon;
};

export const ArrowCounterClockwise = icon(LRotateCcw);
export const ArrowLeft = icon(LArrowLeft);
export const ArrowUpRight = icon(LArrowUpRight);
export const Briefcase = icon(LBriefcase);
export const CaretDown = icon(LChevronDown);
export const CaretLeft = icon(LChevronLeft);
export const CaretRight = icon(LChevronRight);
export const CaretUp = icon(LChevronUp);
export const ChatText = icon(LMessageSquareText);
export const Check = icon(LCheck);
export const CheckCircle = icon(LCircleCheck);
export const CircleNotch = icon(LLoaderCircle);
export const Clock = icon(LClock);
export const ClockCounterClockwise = icon(LHistory);
export const Code = icon(LCode);
export const Compass = icon(LCompass);
export const Copy = icon(LCopy);
export const Cube = icon(LBox);
export const DeviceMobile = icon(LSmartphone);
export const DownloadSimple = icon(LDownload);
export const Envelope = icon(LMail);
export const EnvelopeSimple = icon(LMail);
export const Eye = icon(LEye);
export const EyeSlash = icon(LEyeOff);
export const File = icon(LFile);
export const FileText = icon(LFileText);
export const Files = icon(LFiles);
export const Gauge = icon(LGauge);
export const Gear = icon(LSettings);
export const GlobeHemisphereWest = icon(LGlobe);
export const Image = icon(LImage);
export const ImageSquare = icon(LImage);
export const Lightning = icon(LZap);
export const List = icon(LMenu);
export const MagnifyingGlass = icon(LSearch);
export const MapPin = icon(LMapPin);
export const Monitor = icon(LMonitor);
export const MusicNote = icon(LMusic);
export const PenNib = icon(LPenTool);
export const PencilSimple = icon(LPencil);
export const PencilSimpleLine = icon(LPencilLine);
export const Phone = icon(LPhone);
export const Plus = icon(LPlus);
export const Question = icon(LCircleHelp);
export const Shield = icon(LShield);
export const SignOut = icon(LLogOut);
export const Sparkle = icon(LSparkles);
export const SquaresFour = icon(LLayoutGrid);
export const Stack = icon(LLayers);
export const Star = icon(LStar);
export const Trash = icon(LTrash2);
export const Tray = icon(LInbox);
export const UploadSimple = icon(LUpload);
export const Users = icon(LUsers);
export const VideoCamera = icon(LVideo);
export const WarningCircle = icon(LCircleAlert);
export const X = icon(LX);
