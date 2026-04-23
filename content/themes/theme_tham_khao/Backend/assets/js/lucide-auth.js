/**
 * @license lucide v0.544.0 - ISC
 * Custom Build - Chỉ chứa 41 icons đã sử dụng
 * Generated: 2025-10-10T11:28:53.638Z
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.lucide = {}));
})(this, (function (exports) { 'use strict';

  const defaultAttributes = {
    xmlns: "http://www.w3.org/2000/svg",
    width: 24,
    height: 24,
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    "stroke-width": 2,
    "stroke-linecap": "round",
    "stroke-linejoin": "round"
  };

  const createSVGElement = ([tag, attrs, children]) => {
    const element = document.createElementNS("http://www.w3.org/2000/svg", tag);
    Object.keys(attrs).forEach((name) => {
      element.setAttribute(name, String(attrs[name]));
    });
    if (children?.length) {
      children.forEach((child) => {
        const childElement = createSVGElement(child);
        element.appendChild(childElement);
      });
    }
    return element;
  };

  const createElement = (iconNode, customAttrs = {}) => {
    const tag = "svg";
    const attrs = {
      ...defaultAttributes,
      ...customAttrs
    };
    return createSVGElement([tag, attrs, iconNode]);
  };

  const getAttrs = (element) => Array.from(element.attributes).reduce((attrs, attr) => {
    attrs[attr.name] = attr.value;
    return attrs;
  }, {});

  const getClassNames = (attrs) => {
    if (typeof attrs === "string") return attrs;
    if (!attrs || !attrs.class) return "";
    if (attrs.class && typeof attrs.class === "string") {
      return attrs.class.split(" ");
    }
    if (attrs.class && Array.isArray(attrs.class)) {
      return attrs.class;
    }
    return "";
  };

  const combineClassNames = (arrayOfClassnames) => {
    const classNameArray = arrayOfClassnames.flatMap(getClassNames);
    return classNameArray.map((classItem) => classItem.trim()).filter(Boolean).filter((value, index, self) => self.indexOf(value) === index).join(" ");
  };

  const toPascalCase = (string) => string.replace(/(\w)(\w*)(_|-|\s*)/g, (g0, g1, g2) => g1.toUpperCase() + g2.toLowerCase());

  const replaceElement = (element, { nameAttr, icons, attrs }) => {
    const iconName = element.getAttribute(nameAttr);
    if (iconName == null) return;
    const ComponentName = toPascalCase(iconName);
    const iconNode = icons[ComponentName];
    if (!iconNode) {
      return console.warn(
        `${element.outerHTML} icon name was not found in the provided icons object.`
      );
    }
    const elementAttrs = getAttrs(element);
    const iconAttrs = {
      ...defaultAttributes,
      "data-lucide": iconName,
      ...attrs,
      ...elementAttrs
    };
    const classNames = combineClassNames(["lucide", `lucide-${iconName}`, elementAttrs, attrs]);
    if (classNames) {
      Object.assign(iconAttrs, {
        class: classNames
      });
    }
    const svgElement = createElement(iconNode, iconAttrs);
    return element.parentNode?.replaceChild(svgElement, element);
  };

  const ArrowLeft = [
  [
    "path",
    {
      "d": "m12 19-7-7 7-7"
    }
  ],
  [
    "path",
    {
      "d": "M19 12H5"
    }
  ]
];
  const User = [
  [
    "path",
    {
      "d": "M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "7",
      "r": "4"
    }
  ]
];
  const Briefcase = [
  [
    "path",
    {
      "d": "M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"
    }
  ],
  [
    "rect",
    {
      "width": "20",
      "height": "14",
      "x": "2",
      "y": "6",
      "rx": "2"
    }
  ]
];
  const Share2 = [
  [
    "circle",
    {
      "cx": "18",
      "cy": "5",
      "r": "3"
    }
  ],
  [
    "circle",
    {
      "cx": "6",
      "cy": "12",
      "r": "3"
    }
  ],
  [
    "circle",
    {
      "cx": "18",
      "cy": "19",
      "r": "3"
    }
  ],
  [
    "line",
    {
      "x1": "8.59",
      "x2": "15.42",
      "y1": "13.51",
      "y2": "17.49"
    }
  ],
  [
    "line",
    {
      "x1": "15.41",
      "x2": "8.59",
      "y1": "6.51",
      "y2": "10.49"
    }
  ]
];
  const Shield = [
  [
    "path",
    {
      "d": "M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"
    }
  ]
];
  const Eye = [
  [
    "path",
    {
      "d": "M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "12",
      "r": "3"
    }
  ]
];
  const AtSign = [
  [
    "circle",
    {
      "cx": "12",
      "cy": "12",
      "r": "4"
    }
  ],
  [
    "path",
    {
      "d": "M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"
    }
  ]
];
  const Mail = [
  [
    "path",
    {
      "d": "m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"
    }
  ],
  [
    "rect",
    {
      "x": "2",
      "y": "4",
      "width": "20",
      "height": "16",
      "rx": "2"
    }
  ]
];
  const Calendar = [
  [
    "path",
    {
      "d": "M8 2v4"
    }
  ],
  [
    "path",
    {
      "d": "M16 2v4"
    }
  ],
  [
    "rect",
    {
      "width": "18",
      "height": "18",
      "x": "3",
      "y": "4",
      "rx": "2"
    }
  ],
  [
    "path",
    {
      "d": "M3 10h18"
    }
  ]
];
  const Users = [
  [
    "path",
    {
      "d": "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
    }
  ],
  [
    "path",
    {
      "d": "M16 3.128a4 4 0 0 1 0 7.744"
    }
  ],
  [
    "path",
    {
      "d": "M22 21v-2a4 4 0 0 0-3-3.87"
    }
  ],
  [
    "circle",
    {
      "cx": "9",
      "cy": "7",
      "r": "4"
    }
  ]
];
  const FileText = [
  [
    "path",
    {
      "d": "M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"
    }
  ],
  [
    "path",
    {
      "d": "M14 2v4a2 2 0 0 0 2 2h4"
    }
  ],
  [
    "path",
    {
      "d": "M10 9H8"
    }
  ],
  [
    "path",
    {
      "d": "M16 13H8"
    }
  ],
  [
    "path",
    {
      "d": "M16 17H8"
    }
  ]
];
  const MapPin = [
  [
    "path",
    {
      "d": "M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "10",
      "r": "3"
    }
  ]
];
  const Phone = [
  [
    "path",
    {
      "d": "M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"
    }
  ]
];
  const Flag = [
  [
    "path",
    {
      "d": "M4 22V4a1 1 0 0 1 .4-.8A6 6 0 0 1 8 2c3 0 5 2 7.333 2q2 0 3.067-.8A1 1 0 0 1 20 4v10a1 1 0 0 1-.4.8A6 6 0 0 1 16 16c-3 0-5-2-8-2a6 6 0 0 0-4 1.528"
    }
  ]
];
  const Home = [
  [
    "path",
    {
      "d": "M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"
    }
  ],
  [
    "path",
    {
      "d": "M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"
    }
  ]
];
  const Building = [
  [
    "path",
    {
      "d": "M12 10h.01"
    }
  ],
  [
    "path",
    {
      "d": "M12 14h.01"
    }
  ],
  [
    "path",
    {
      "d": "M12 6h.01"
    }
  ],
  [
    "path",
    {
      "d": "M16 10h.01"
    }
  ],
  [
    "path",
    {
      "d": "M16 14h.01"
    }
  ],
  [
    "path",
    {
      "d": "M16 6h.01"
    }
  ],
  [
    "path",
    {
      "d": "M8 10h.01"
    }
  ],
  [
    "path",
    {
      "d": "M8 14h.01"
    }
  ],
  [
    "path",
    {
      "d": "M8 6h.01"
    }
  ],
  [
    "path",
    {
      "d": "M9 22v-3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"
    }
  ],
  [
    "rect",
    {
      "x": "4",
      "y": "2",
      "width": "16",
      "height": "20",
      "rx": "2"
    }
  ]
];
  const Map = [
  [
    "path",
    {
      "d": "M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"
    }
  ],
  [
    "path",
    {
      "d": "M15 5.764v15"
    }
  ],
  [
    "path",
    {
      "d": "M9 3.236v15"
    }
  ]
];
  const Hash = [
  [
    "line",
    {
      "x1": "4",
      "x2": "20",
      "y1": "9",
      "y2": "9"
    }
  ],
  [
    "line",
    {
      "x1": "4",
      "x2": "20",
      "y1": "15",
      "y2": "15"
    }
  ],
  [
    "line",
    {
      "x1": "10",
      "x2": "8",
      "y1": "3",
      "y2": "21"
    }
  ],
  [
    "line",
    {
      "x1": "16",
      "x2": "14",
      "y1": "3",
      "y2": "21"
    }
  ]
];
  const Save = [
  [
    "path",
    {
      "d": "M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"
    }
  ],
  [
    "path",
    {
      "d": "M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"
    }
  ],
  [
    "path",
    {
      "d": "M7 3v4a1 1 0 0 0 1 1h7"
    }
  ]
];
  const Plus = [
  [
    "path",
    {
      "d": "M5 12h14"
    }
  ],
  [
    "path",
    {
      "d": "M12 5v14"
    }
  ]
];
  const Trash2 = [
  [
    "path",
    {
      "d": "M10 11v6"
    }
  ],
  [
    "path",
    {
      "d": "M14 11v6"
    }
  ],
  [
    "path",
    {
      "d": "M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
    }
  ],
  [
    "path",
    {
      "d": "M3 6h18"
    }
  ],
  [
    "path",
    {
      "d": "M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
    }
  ]
];
  const GraduationCap = [
  [
    "path",
    {
      "d": "M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"
    }
  ],
  [
    "path",
    {
      "d": "M22 10v6"
    }
  ],
  [
    "path",
    {
      "d": "M6 12.5V16a6 3 0 0 0 12 0v-3.5"
    }
  ]
];
  const Award = [
  [
    "path",
    {
      "d": "m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "8",
      "r": "6"
    }
  ]
];
  const X = [
  [
    "path",
    {
      "d": "M18 6 6 18"
    }
  ],
  [
    "path",
    {
      "d": "m6 6 12 12"
    }
  ]
];
  const AlertTriangle = [
  [
    "path",
    {
      "d": "m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"
    }
  ],
  [
    "path",
    {
      "d": "M12 9v4"
    }
  ],
  [
    "path",
    {
      "d": "M12 17h.01"
    }
  ]
];
  const Globe = [
  [
    "circle",
    {
      "cx": "12",
      "cy": "12",
      "r": "10"
    }
  ],
  [
    "path",
    {
      "d": "M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"
    }
  ],
  [
    "path",
    {
      "d": "M2 12h20"
    }
  ]
];
  const Heart = [
  [
    "path",
    {
      "d": "M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"
    }
  ]
];
  const Facebook = [
  [
    "path",
    {
      "d": "M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"
    }
  ]
];
  const MessageCircle = [
  [
    "path",
    {
      "d": "M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"
    }
  ]
];
  const Smartphone = [
  [
    "rect",
    {
      "width": "14",
      "height": "20",
      "x": "5",
      "y": "2",
      "rx": "2",
      "ry": "2"
    }
  ],
  [
    "path",
    {
      "d": "M12 18h.01"
    }
  ]
];
  const Linkedin = [
  [
    "path",
    {
      "d": "M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"
    }
  ],
  [
    "rect",
    {
      "width": "4",
      "height": "12",
      "x": "2",
      "y": "9"
    }
  ],
  [
    "circle",
    {
      "cx": "4",
      "cy": "4",
      "r": "2"
    }
  ]
];
  const Link = [
  [
    "path",
    {
      "d": "M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"
    }
  ],
  [
    "path",
    {
      "d": "M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"
    }
  ]
];
  const Lock = [
  [
    "rect",
    {
      "width": "18",
      "height": "11",
      "x": "3",
      "y": "11",
      "rx": "2",
      "ry": "2"
    }
  ],
  [
    "path",
    {
      "d": "M7 11V7a5 5 0 0 1 10 0v4"
    }
  ]
];
  const Key = [
  [
    "path",
    {
      "d": "m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"
    }
  ],
  [
    "path",
    {
      "d": "m21 2-9.6 9.6"
    }
  ],
  [
    "circle",
    {
      "cx": "7.5",
      "cy": "15.5",
      "r": "5.5"
    }
  ]
];
  const ShieldCheck = [
  [
    "path",
    {
      "d": "M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"
    }
  ],
  [
    "path",
    {
      "d": "m9 12 2 2 4-4"
    }
  ]
];
  const LogOut = [
  [
    "path",
    {
      "d": "m16 17 5-5-5-5"
    }
  ],
  [
    "path",
    {
      "d": "M21 12H9"
    }
  ],
  [
    "path",
    {
      "d": "M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
    }
  ]
];
  const LogIn = [
  [
    "path",
    {
      "d": "m10 17 5-5-5-5"
    }
  ],
  [
    "path",
    {
      "d": "M15 12H3"
    }
  ],
  [
    "path",
    {
      "d": "M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"
    }
  ]
];
  const Chrome = [
  [
    "path",
    {
      "d": "M10.88 21.94 15.46 14"
    }
  ],
  [
    "path",
    {
      "d": "M21.17 8H12"
    }
  ],
  [
    "path",
    {
      "d": "M3.95 6.06 8.54 14"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "12",
      "r": "10"
    }
  ],
  [
    "circle",
    {
      "cx": "12",
      "cy": "12",
      "r": "4"
    }
  ]
];
  const Check = [
  [
    "path",
    {
      "d": "M20 6 9 17l-5-5"
    }
  ]
];
  const UserPlus = [
  [
    "path",
    {
      "d": "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
    }
  ],
  [
    "circle",
    {
      "cx": "9",
      "cy": "7",
      "r": "4"
    }
  ],
  [
    "line",
    {
      "x1": "19",
      "x2": "19",
      "y1": "8",
      "y2": "14"
    }
  ],
  [
    "line",
    {
      "x1": "22",
      "x2": "16",
      "y1": "11",
      "y2": "11"
    }
  ]
];
  const Loader2 = [
  [
    "path",
    {
      "d": "M21 12a9 9 0 1 1-6.219-8.56"
    }
  ]
];

  var iconAndAliases = /*#__PURE__*/Object.freeze({
    __proto__: null,
    ArrowLeft: ArrowLeft,
    User: User,
    Briefcase: Briefcase,
    Share2: Share2,
    Shield: Shield,
    Eye: Eye,
    AtSign: AtSign,
    Mail: Mail,
    Calendar: Calendar,
    Users: Users,
    FileText: FileText,
    MapPin: MapPin,
    Phone: Phone,
    Flag: Flag,
    Home: Home,
    Building: Building,
    Map: Map,
    Hash: Hash,
    Save: Save,
    Plus: Plus,
    Trash2: Trash2,
    GraduationCap: GraduationCap,
    Award: Award,
    X: X,
    AlertTriangle: AlertTriangle,
    Globe: Globe,
    Heart: Heart,
    Facebook: Facebook,
    MessageCircle: MessageCircle,
    Smartphone: Smartphone,
    Linkedin: Linkedin,
    Link: Link,
    Lock: Lock,
    Key: Key,
    ShieldCheck: ShieldCheck,
    LogOut: LogOut,
    LogIn: LogIn,
    Chrome: Chrome,
    Check: Check,
    UserPlus: UserPlus,
    Loader2: Loader2,
  });

  const createIcons = ({
    icons = iconAndAliases,
    nameAttr = "data-lucide",
    attrs = {},
    root = document
  } = {}) => {
    if (!Object.values(icons).length) {
      throw new Error(
        "Please provide an icons object.If you want to use all the icons you can import it like: import { createIcons, icons } from 'lucide'; lucide.createIcons({icons});"
      );
    }
    if (typeof root === "undefined") {
      throw new Error("`createIcons()` only works in a browser environment.");
    }
    const elementsToReplace = root.querySelectorAll(`[${nameAttr}]`);
    Array.from(elementsToReplace).forEach(
      (element) => replaceElement(element, { nameAttr, icons, attrs })
    );
    if (nameAttr === "data-lucide") {
      const deprecatedElements = root.querySelectorAll("[icon-name]");
      if (deprecatedElements.length > 0) {
        console.warn(
          "[Lucide] Some icons were found with the now deprecated icon-name attribute. These will still be replaced for backwards compatibility, but will no longer be supported in v1.0 and you should switch to data-lucide"
        );
        Array.from(deprecatedElements).forEach(
          (element) => replaceElement(element, { nameAttr: "icon-name", icons, attrs })
        );
      }
    }
  };

  exports.ArrowLeft = ArrowLeft;
  exports.User = User;
  exports.Briefcase = Briefcase;
  exports.Share2 = Share2;
  exports.Shield = Shield;
  exports.Eye = Eye;
  exports.AtSign = AtSign;
  exports.Mail = Mail;
  exports.Calendar = Calendar;
  exports.Users = Users;
  exports.FileText = FileText;
  exports.MapPin = MapPin;
  exports.Phone = Phone;
  exports.Flag = Flag;
  exports.Home = Home;
  exports.Building = Building;
  exports.Map = Map;
  exports.Hash = Hash;
  exports.Save = Save;
  exports.Plus = Plus;
  exports.Trash2 = Trash2;
  exports.GraduationCap = GraduationCap;
  exports.Award = Award;
  exports.X = X;
  exports.AlertTriangle = AlertTriangle;
  exports.Globe = Globe;
  exports.Heart = Heart;
  exports.Facebook = Facebook;
  exports.MessageCircle = MessageCircle;
  exports.Smartphone = Smartphone;
  exports.Linkedin = Linkedin;
  exports.Link = Link;
  exports.Lock = Lock;
  exports.Key = Key;
  exports.ShieldCheck = ShieldCheck;
  exports.LogOut = LogOut;
  exports.LogIn = LogIn;
  exports.Chrome = Chrome;
  exports.Check = Check;
  exports.UserPlus = UserPlus;
  exports.Loader2 = Loader2;
  exports.icons = iconAndAliases;
  exports.createIcons = createIcons;

}));
//# sourceMappingURL=lucide-custom.js.map