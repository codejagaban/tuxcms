// The dashboard's icons: Phosphor, all drawn at the one regular weight so
// thin, bold and filled marks never mix on a screen.
import {
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
export const GlobeHemisphereWest = icon(PGlobeHemisphereWest);
export const Image = icon(PImage);
export const ImageSquare = icon(PImageSquare);
export const Lightning = icon(PLightning);
export const List = icon(PList);
export const MagnifyingGlass = icon(PMagnifyingGlass);
export const MapPin = icon(PMapPin);
export const Monitor = icon(PMonitor);
export const MusicNote = icon(PMusicNote);
export const PenNib = icon(PPenNib);
export const PencilSimple = icon(PPencilSimple);
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
export const Trash = icon(PTrash);
export const Tray = icon(PTray);
export const UploadSimple = icon(PUploadSimple);
export const Users = icon(PUsers);
export const VideoCamera = icon(PVideoCamera);
export const WarningCircle = icon(PWarningCircle);
export const X = icon(PX);
