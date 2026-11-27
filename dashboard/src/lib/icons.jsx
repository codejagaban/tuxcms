// The dashboard's icons: Phosphor, all drawn at the one regular weight so
// thin, bold and filled marks never mix on a screen.
import {
  ArrowClockwise as PArrowClockwise,
  LinkSimple as PLinkSimple,
  ListBullets as PListBullets,
  ListNumbers as PListNumbers,
  Minus as PMinus,
  Quotes as PQuotes,
  TextB as PTextB,
  TextHThree as PTextHThree,
  TextHTwo as PTextHTwo,
  TextItalic as PTextItalic,
  TextStrikethrough as PTextStrikethrough,
  TextUnderline as PTextUnderline,
  ArrowCounterClockwise as PArrowCounterClockwise,
  ArrowLeft as PArrowLeft,
  ArrowUpRight as PArrowUpRight,
  Briefcase as PBriefcase,
  CaretDown as PCaretDown,
  CaretLeft as PCaretLeft,
  CaretRight as PCaretRight,
  CaretUp as PCaretUp,
  ChatText as PChatText,
  Check as PCheck,
  CheckCircle as PCheckCircle,
  CircleNotch as PCircleNotch,
  Clock as PClock,
  ClockCounterClockwise as PClockCounterClockwise,
  Code as PCode,
  Compass as PCompass,
  Copy as PCopy,
  Cube as PCube,
  DeviceMobile as PDeviceMobile,
  DownloadSimple as PDownloadSimple,
  Envelope as PEnvelope,
  EnvelopeSimple as PEnvelopeSimple,
  Eye as PEye,
  EyeSlash as PEyeSlash,
  File as PFile,
  FileText as PFileText,
  Files as PFiles,
  Gauge as PGauge,
  Gear as PGear,
  GlobeHemisphereWest as PGlobeHemisphereWest,
  Image as PImage,
  ImageSquare as PImageSquare,
  Lightning as PLightning,
  List as PList,
  MagnifyingGlass as PMagnifyingGlass,
  MapPin as PMapPin,
  Monitor as PMonitor,
  MusicNote as PMusicNote,
  PenNib as PPenNib,
  PencilSimple as PPencilSimple,
  PencilSimpleLine as PPencilSimpleLine,
  Phone as PPhone,
  Plus as PPlus,
  Question as PQuestion,
  Shield as PShield,
  SignOut as PSignOut,
  Sparkle as PSparkle,
  SquaresFour as PSquaresFour,
  Stack as PStack,
  Star as PStar,
  Trash as PTrash,
  Tray as PTray,
  UploadSimple as PUploadSimple,
  Users as PUsers,
  VideoCamera as PVideoCamera,
  WarningCircle as PWarningCircle,
  X as PX,
} from '@phosphor-icons/react';

const icon = (Glyph) => {
  // A screen may still ask for a weight; every icon ignores it and stays regular.
  // eslint-disable-next-line no-unused-vars
  const Icon = ({ weight, ...props }) => <Glyph weight="regular" aria-hidden="true" {...props} />;
  return Icon;
};

// The post editor's formatting marks.
export const ArrowClockwise = icon(PArrowClockwise);
export const LinkSimple = icon(PLinkSimple);
export const ListBullets = icon(PListBullets);
export const ListNumbers = icon(PListNumbers);
export const Minus = icon(PMinus);
export const Quotes = icon(PQuotes);
export const TextB = icon(PTextB);
export const TextHThree = icon(PTextHThree);
export const TextHTwo = icon(PTextHTwo);
export const TextItalic = icon(PTextItalic);
export const TextStrikethrough = icon(PTextStrikethrough);
export const TextUnderline = icon(PTextUnderline);

export const ArrowCounterClockwise = icon(PArrowCounterClockwise);
export const ArrowLeft = icon(PArrowLeft);
export const ArrowUpRight = icon(PArrowUpRight);
export const Briefcase = icon(PBriefcase);
export const CaretDown = icon(PCaretDown);
export const CaretLeft = icon(PCaretLeft);
export const CaretRight = icon(PCaretRight);
export const CaretUp = icon(PCaretUp);
export const ChatText = icon(PChatText);
export const Check = icon(PCheck);
export const CheckCircle = icon(PCheckCircle);
export const CircleNotch = icon(PCircleNotch);
export const Clock = icon(PClock);
export const ClockCounterClockwise = icon(PClockCounterClockwise);
export const Code = icon(PCode);
export const Compass = icon(PCompass);
export const Copy = icon(PCopy);
export const Cube = icon(PCube);
export const DeviceMobile = icon(PDeviceMobile);
export const DownloadSimple = icon(PDownloadSimple);
export const Envelope = icon(PEnvelope);
export const EnvelopeSimple = icon(PEnvelopeSimple);
export const Eye = icon(PEye);
export const EyeSlash = icon(PEyeSlash);
export const File = icon(PFile);
export const FileText = icon(PFileText);
export const Files = icon(PFiles);
export const Gauge = icon(PGauge);
export const Gear = icon(PGear);
export const GlobeHemisphereWest = (props) => <IconPublished {...props} />;
export const Image = icon(PImage);
export const ImageSquare = icon(PImageSquare);
export const Lightning = icon(PLightning);
export const List = icon(PList);
export const MagnifyingGlass = icon(PMagnifyingGlass);
export const MapPin = icon(PMapPin);
export const Monitor = icon(PMonitor);
export const MusicNote = icon(PMusicNote);
export const PenNib = icon(PPenNib);
export const PencilSimple = (props) => <IconEdit {...props} />;
export const PencilSimpleLine = icon(PPencilSimpleLine);
export const Phone = icon(PPhone);
export const Plus = icon(PPlus);
export const Question = icon(PQuestion);
export const Shield = icon(PShield);
export const SignOut = icon(PSignOut);
export const Sparkle = icon(PSparkle);
export const SquaresFour = icon(PSquaresFour);
export const Stack = icon(PStack);
export const Star = icon(PStar);
export const Trash = (props) => <IconDelete {...props} />;
export const Tray = icon(PTray);
export const UploadSimple = icon(PUploadSimple);
export const Users = icon(PUsers);
export const VideoCamera = icon(PVideoCamera);
export const WarningCircle = icon(PWarningCircle);
export const X = icon(PX);

// Icons drawn for this dashboard, where a library mark was too generic to say
// what a section holds. One family: a 24px grid, a 1.5px line, round ends.
const drawn = (paths) => {
  // eslint-disable-next-line no-unused-vars
  const Icon = ({ weight, ...props }) => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
      {paths}
    </svg>
  );
  return Icon;
};

export const IconOverview = drawn(
  <>
    <rect x="3" y="4" width="18" height="16" rx="2.5" />
    <path d="M3 8.5h18" />
    <path d="M8.6 14.3l2.3 2.3 4.6-4.8" />
  </>
);

export const IconPages = drawn(
  <>
    <rect x="4" y="6" width="13" height="15" rx="2" />
    <path d="M8 6V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-1" />
    <path d="M7.5 10.5h4M7.5 14h6M7.5 17h4" />
  </>
);

// A written post: a sheet with a picture at the top and lines beneath it.
export const IconBlog = drawn(
  <>
    <rect x="4" y="3" width="16" height="18" rx="2.5" />
    <rect x="7.5" y="6.5" width="9" height="4.5" rx="1" />
    <path d="M7.5 14.5h9M7.5 17.5h5.5" />
  </>
);

export const IconJobs = drawn(
  <>
    <rect x="5" y="6" width="14" height="15" rx="2.5" />
    <path d="M10 6V4.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1V6" />
    <circle cx="12" cy="12" r="2" />
    <path d="M8.6 17.8c.6-1.9 1.9-2.8 3.4-2.8s2.8.9 3.4 2.8" />
  </>
);

export const IconEnquiries = drawn(
  <>
    <rect x="6" y="3" width="12" height="8.5" rx="1.5" />
    <path d="M6.6 4.2L12 8.2l5.4-4" />
    <path d="M3 14v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4" />
    <path d="M3 14h4.5l1 2h7l1-2H21" />
  </>
);

export const IconApplications = drawn(
  <>
    <path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z" />
    <path d="M14 3v3.5A1.5 1.5 0 0 0 15.5 8H19" />
    <circle cx="9.6" cy="10.4" r="1.6" />
    <path d="M8.5 15h7M8.5 18h4.5" />
  </>
);

export const IconMedia = drawn(
  <>
    <rect x="3" y="7" width="15" height="14" rx="2" />
    <path d="M7.5 7V5a2 2 0 0 1 2-2H19a2 2 0 0 1 2 2v9.5a2 2 0 0 1-2 2h-1" />
    <circle cx="7.5" cy="11.5" r="1.4" />
    <path d="M3.3 18.2l3.9-3.9 3 3 2.5-2.5 4.9 4.9" />
  </>
);

export const IconSearch = drawn(
  <>
    <path d="M3 6h9M3 11h5M3 16h4" />
    <circle cx="15" cy="13" r="4.5" />
    <path d="M18.3 16.3l3.2 3.2" />
  </>
);

export const IconSettings = drawn(
  <>
    <path d="M4 7h8.5M16.5 7H20M4 12h2.5M10.5 12H20M4 17h9M17 17h3" />
    <circle cx="14.5" cy="7" r="2" />
    <circle cx="8.5" cy="12" r="2" />
    <circle cx="15" cy="17" r="2" />
  </>
);

export const IconPublished = drawn(
  <>
    <circle cx="12" cy="12" r="8.5" />
    <path d="M12 3.5c2.5 2.6 3.5 5.4 3.5 8.5s-1 5.9-3.5 8.5c-2.5-2.6-3.5-5.4-3.5-8.5s1-5.9 3.5-8.5z" />
    <path d="M3.5 12h17" />
  </>
);

export const IconDrafts = drawn(
  <>
    <path d="M13 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5" />
    <path d="M8.5 8h5M8.5 12h3" />
    <path d="M14.2 19.8l-3 .7.7-3 6.6-6.6a1.63 1.63 0 0 1 2.3 2.3z" />
  </>
);

export const IconEdit = drawn(
  <>
    <path d="M4 20l1-4.2L16.3 4.5a2.12 2.12 0 0 1 3 3L8 18.9z" />
    <path d="M14.2 6.6l3 3" />
  </>
);

export const IconDelete = drawn(
  <>
    <path d="M4.5 7h15" />
    <path d="M9.5 7V5A1.5 1.5 0 0 1 11 3.5h2A1.5 1.5 0 0 1 14.5 5v2" />
    <path d="M6.5 7l.8 11.2a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7" />
    <path d="M10.3 11v5M13.7 11v5" />
  </>
);
