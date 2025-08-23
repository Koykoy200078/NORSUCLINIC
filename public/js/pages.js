/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/flatpickr/dist/l10n/index.js":
/*!***************************************************!*\
  !*** ./node_modules/flatpickr/dist/l10n/index.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports) {

(function (global, factory) {
     true ? factory(exports) :
    0;
}(this, (function (exports) { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */

    var __assign = function() {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };

    var fp = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Arabic = {
        weekdays: {
            shorthand: ["أحد", "اثنين", "ثلاثاء", "أربعاء", "خميس", "جمعة", "سبت"],
            longhand: [
                "الأحد",
                "الاثنين",
                "الثلاثاء",
                "الأربعاء",
                "الخميس",
                "الجمعة",
                "السبت",
            ],
        },
        months: {
            shorthand: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
            longhand: [
                "يناير",
                "فبراير",
                "مارس",
                "أبريل",
                "مايو",
                "يونيو",
                "يوليو",
                "أغسطس",
                "سبتمبر",
                "أكتوبر",
                "نوفمبر",
                "ديسمبر",
            ],
        },
        firstDayOfWeek: 6,
        rangeSeparator: " إلى ",
        weekAbbreviation: "Wk",
        scrollTitle: "قم بالتمرير للزيادة",
        toggleTitle: "اضغط للتبديل",
        amPM: ["ص", "م"],
        yearAriaLabel: "سنة",
        monthAriaLabel: "شهر",
        hourAriaLabel: "ساعة",
        minuteAriaLabel: "دقيقة",
        time_24hr: false,
    };
    fp.l10ns.ar = Arabic;
    fp.l10ns;

    var fp$1 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Austria = {
        weekdays: {
            shorthand: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            longhand: [
                "Sonntag",
                "Montag",
                "Dienstag",
                "Mittwoch",
                "Donnerstag",
                "Freitag",
                "Samstag",
            ],
        },
        months: {
            shorthand: [
                "Jän",
                "Feb",
                "Mär",
                "Apr",
                "Mai",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dez",
            ],
            longhand: [
                "Jänner",
                "Februar",
                "März",
                "April",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "September",
                "Oktober",
                "November",
                "Dezember",
            ],
        },
        firstDayOfWeek: 1,
        weekAbbreviation: "KW",
        rangeSeparator: " bis ",
        scrollTitle: "Zum Ändern scrollen",
        toggleTitle: "Zum Umschalten klicken",
        time_24hr: true,
    };
    fp$1.l10ns.at = Austria;
    fp$1.l10ns;

    var fp$2 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Azerbaijan = {
        weekdays: {
            shorthand: ["B.", "B.e.", "Ç.a.", "Ç.", "C.a.", "C.", "Ş."],
            longhand: [
                "Bazar",
                "Bazar ertəsi",
                "Çərşənbə axşamı",
                "Çərşənbə",
                "Cümə axşamı",
                "Cümə",
                "Şənbə",
            ],
        },
        months: {
            shorthand: [
                "Yan",
                "Fev",
                "Mar",
                "Apr",
                "May",
                "İyn",
                "İyl",
                "Avq",
                "Sen",
                "Okt",
                "Noy",
                "Dek",
            ],
            longhand: [
                "Yanvar",
                "Fevral",
                "Mart",
                "Aprel",
                "May",
                "İyun",
                "İyul",
                "Avqust",
                "Sentyabr",
                "Oktyabr",
                "Noyabr",
                "Dekabr",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return ".";
        },
        rangeSeparator: " - ",
        weekAbbreviation: "Hf",
        scrollTitle: "Artırmaq üçün sürüşdürün",
        toggleTitle: "Aç / Bağla",
        amPM: ["GƏ", "GS"],
        time_24hr: true,
    };
    fp$2.l10ns.az = Azerbaijan;
    fp$2.l10ns;

    var fp$3 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Belarusian = {
        weekdays: {
            shorthand: ["Нд", "Пн", "Аў", "Ср", "Чц", "Пт", "Сб"],
            longhand: [
                "Нядзеля",
                "Панядзелак",
                "Аўторак",
                "Серада",
                "Чацвер",
                "Пятніца",
                "Субота",
            ],
        },
        months: {
            shorthand: [
                "Сту",
                "Лют",
                "Сак",
                "Кра",
                "Тра",
                "Чэр",
                "Ліп",
                "Жні",
                "Вер",
                "Кас",
                "Ліс",
                "Сне",
            ],
            longhand: [
                "Студзень",
                "Люты",
                "Сакавік",
                "Красавік",
                "Травень",
                "Чэрвень",
                "Ліпень",
                "Жнівень",
                "Верасень",
                "Кастрычнік",
                "Лістапад",
                "Снежань",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Тыд.",
        scrollTitle: "Пракруціце для павелічэння",
        toggleTitle: "Націсніце для пераключэння",
        amPM: ["ДП", "ПП"],
        yearAriaLabel: "Год",
        time_24hr: true,
    };
    fp$3.l10ns.be = Belarusian;
    fp$3.l10ns;

    var fp$4 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Bosnian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["Ned", "Pon", "Uto", "Sri", "Čet", "Pet", "Sub"],
            longhand: [
                "Nedjelja",
                "Ponedjeljak",
                "Utorak",
                "Srijeda",
                "Četvrtak",
                "Petak",
                "Subota",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Maj",
                "Jun",
                "Jul",
                "Avg",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Mart",
                "April",
                "Maj",
                "Juni",
                "Juli",
                "Avgust",
                "Septembar",
                "Oktobar",
                "Novembar",
                "Decembar",
            ],
        },
        time_24hr: true,
    };
    fp$4.l10ns.bs = Bosnian;
    fp$4.l10ns;

    var fp$5 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Bulgarian = {
        weekdays: {
            shorthand: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            longhand: [
                "Неделя",
                "Понеделник",
                "Вторник",
                "Сряда",
                "Четвъртък",
                "Петък",
                "Събота",
            ],
        },
        months: {
            shorthand: [
                "Яну",
                "Фев",
                "Март",
                "Апр",
                "Май",
                "Юни",
                "Юли",
                "Авг",
                "Сеп",
                "Окт",
                "Ное",
                "Дек",
            ],
            longhand: [
                "Януари",
                "Февруари",
                "Март",
                "Април",
                "Май",
                "Юни",
                "Юли",
                "Август",
                "Септември",
                "Октомври",
                "Ноември",
                "Декември",
            ],
        },
        time_24hr: true,
        firstDayOfWeek: 1,
    };
    fp$5.l10ns.bg = Bulgarian;
    fp$5.l10ns;

    var fp$6 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Bangla = {
        weekdays: {
            shorthand: ["রবি", "সোম", "মঙ্গল", "বুধ", "বৃহস্পতি", "শুক্র", "শনি"],
            longhand: [
                "রবিবার",
                "সোমবার",
                "মঙ্গলবার",
                "বুধবার",
                "বৃহস্পতিবার",
                "শুক্রবার",
                "শনিবার",
            ],
        },
        months: {
            shorthand: [
                "জানু",
                "ফেব্রু",
                "মার্চ",
                "এপ্রিল",
                "মে",
                "জুন",
                "জুলাই",
                "আগ",
                "সেপ্টে",
                "অক্টো",
                "নভে",
                "ডিসে",
            ],
            longhand: [
                "জানুয়ারী",
                "ফেব্রুয়ারী",
                "মার্চ",
                "এপ্রিল",
                "মে",
                "জুন",
                "জুলাই",
                "আগস্ট",
                "সেপ্টেম্বর",
                "অক্টোবর",
                "নভেম্বর",
                "ডিসেম্বর",
            ],
        },
    };
    fp$6.l10ns.bn = Bangla;
    fp$6.l10ns;

    var fp$7 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Catalan = {
        weekdays: {
            shorthand: ["Dg", "Dl", "Dt", "Dc", "Dj", "Dv", "Ds"],
            longhand: [
                "Diumenge",
                "Dilluns",
                "Dimarts",
                "Dimecres",
                "Dijous",
                "Divendres",
                "Dissabte",
            ],
        },
        months: {
            shorthand: [
                "Gen",
                "Febr",
                "Març",
                "Abr",
                "Maig",
                "Juny",
                "Jul",
                "Ag",
                "Set",
                "Oct",
                "Nov",
                "Des",
            ],
            longhand: [
                "Gener",
                "Febrer",
                "Març",
                "Abril",
                "Maig",
                "Juny",
                "Juliol",
                "Agost",
                "Setembre",
                "Octubre",
                "Novembre",
                "Desembre",
            ],
        },
        ordinal: function (nth) {
            var s = nth % 100;
            if (s > 3 && s < 21)
                return "è";
            switch (s % 10) {
                case 1:
                    return "r";
                case 2:
                    return "n";
                case 3:
                    return "r";
                case 4:
                    return "t";
                default:
                    return "è";
            }
        },
        firstDayOfWeek: 1,
        rangeSeparator: " a ",
        time_24hr: true,
    };
    fp$7.l10ns.cat = fp$7.l10ns.ca = Catalan;
    fp$7.l10ns;

    var fp$8 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Kurdish = {
        weekdays: {
            shorthand: [
                "یەکشەممە",
                "دووشەممە",
                "سێشەممە",
                "چوارشەممە",
                "پێنجشەممە",
                "هەینی",
                "شەممە",
            ],
            longhand: [
                "یەکشەممە",
                "دووشەممە",
                "سێشەممە",
                "چوارشەممە",
                "پێنجشەممە",
                "هەینی",
                "شەممە",
            ],
        },
        months: {
            shorthand: [
                "ڕێبەندان",
                "ڕەشەمە",
                "نەورۆز",
                "گوڵان",
                "جۆزەردان",
                "پووشپەڕ",
                "گەلاوێژ",
                "خەرمانان",
                "ڕەزبەر",
                "گەڵاڕێزان",
                "سەرماوەز",
                "بەفرانبار",
            ],
            longhand: [
                "ڕێبەندان",
                "ڕەشەمە",
                "نەورۆز",
                "گوڵان",
                "جۆزەردان",
                "پووشپەڕ",
                "گەلاوێژ",
                "خەرمانان",
                "ڕەزبەر",
                "گەڵاڕێزان",
                "سەرماوەز",
                "بەفرانبار",
            ],
        },
        firstDayOfWeek: 6,
        ordinal: function () {
            return "";
        },
    };
    fp$8.l10ns.ckb = Kurdish;
    fp$8.l10ns;

    var fp$9 = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Czech = {
        weekdays: {
            shorthand: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
            longhand: [
                "Neděle",
                "Pondělí",
                "Úterý",
                "Středa",
                "Čtvrtek",
                "Pátek",
                "Sobota",
            ],
        },
        months: {
            shorthand: [
                "Led",
                "Ún",
                "Bře",
                "Dub",
                "Kvě",
                "Čer",
                "Čvc",
                "Srp",
                "Zář",
                "Říj",
                "Lis",
                "Pro",
            ],
            longhand: [
                "Leden",
                "Únor",
                "Březen",
                "Duben",
                "Květen",
                "Červen",
                "Červenec",
                "Srpen",
                "Září",
                "Říjen",
                "Listopad",
                "Prosinec",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return ".";
        },
        rangeSeparator: " do ",
        weekAbbreviation: "Týd.",
        scrollTitle: "Rolujte pro změnu",
        toggleTitle: "Přepnout dopoledne/odpoledne",
        amPM: ["dop.", "odp."],
        yearAriaLabel: "Rok",
        time_24hr: true,
    };
    fp$9.l10ns.cs = Czech;
    fp$9.l10ns;

    var fp$a = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Welsh = {
        weekdays: {
            shorthand: ["Sul", "Llun", "Maw", "Mer", "Iau", "Gwe", "Sad"],
            longhand: [
                "Dydd Sul",
                "Dydd Llun",
                "Dydd Mawrth",
                "Dydd Mercher",
                "Dydd Iau",
                "Dydd Gwener",
                "Dydd Sadwrn",
            ],
        },
        months: {
            shorthand: [
                "Ion",
                "Chwef",
                "Maw",
                "Ebr",
                "Mai",
                "Meh",
                "Gorff",
                "Awst",
                "Medi",
                "Hyd",
                "Tach",
                "Rhag",
            ],
            longhand: [
                "Ionawr",
                "Chwefror",
                "Mawrth",
                "Ebrill",
                "Mai",
                "Mehefin",
                "Gorffennaf",
                "Awst",
                "Medi",
                "Hydref",
                "Tachwedd",
                "Rhagfyr",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function (nth) {
            if (nth === 1)
                return "af";
            if (nth === 2)
                return "ail";
            if (nth === 3 || nth === 4)
                return "ydd";
            if (nth === 5 || nth === 6)
                return "ed";
            if ((nth >= 7 && nth <= 10) ||
                nth == 12 ||
                nth == 15 ||
                nth == 18 ||
                nth == 20)
                return "fed";
            if (nth == 11 ||
                nth == 13 ||
                nth == 14 ||
                nth == 16 ||
                nth == 17 ||
                nth == 19)
                return "eg";
            if (nth >= 21 && nth <= 39)
                return "ain";
            // Inconclusive.
            return "";
        },
        time_24hr: true,
    };
    fp$a.l10ns.cy = Welsh;
    fp$a.l10ns;

    var fp$b = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Danish = {
        weekdays: {
            shorthand: ["søn", "man", "tir", "ons", "tors", "fre", "lør"],
            longhand: [
                "søndag",
                "mandag",
                "tirsdag",
                "onsdag",
                "torsdag",
                "fredag",
                "lørdag",
            ],
        },
        months: {
            shorthand: [
                "jan",
                "feb",
                "mar",
                "apr",
                "maj",
                "jun",
                "jul",
                "aug",
                "sep",
                "okt",
                "nov",
                "dec",
            ],
            longhand: [
                "januar",
                "februar",
                "marts",
                "april",
                "maj",
                "juni",
                "juli",
                "august",
                "september",
                "oktober",
                "november",
                "december",
            ],
        },
        ordinal: function () {
            return ".";
        },
        firstDayOfWeek: 1,
        rangeSeparator: " til ",
        weekAbbreviation: "uge",
        time_24hr: true,
    };
    fp$b.l10ns.da = Danish;
    fp$b.l10ns;

    var fp$c = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var German = {
        weekdays: {
            shorthand: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            longhand: [
                "Sonntag",
                "Montag",
                "Dienstag",
                "Mittwoch",
                "Donnerstag",
                "Freitag",
                "Samstag",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mär",
                "Apr",
                "Mai",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dez",
            ],
            longhand: [
                "Januar",
                "Februar",
                "März",
                "April",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "September",
                "Oktober",
                "November",
                "Dezember",
            ],
        },
        firstDayOfWeek: 1,
        weekAbbreviation: "KW",
        rangeSeparator: " bis ",
        scrollTitle: "Zum Ändern scrollen",
        toggleTitle: "Zum Umschalten klicken",
        time_24hr: true,
    };
    fp$c.l10ns.de = German;
    fp$c.l10ns;

    var english = {
        weekdays: {
            shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            longhand: [
                "Sunday",
                "Monday",
                "Tuesday",
                "Wednesday",
                "Thursday",
                "Friday",
                "Saturday",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec",
            ],
            longhand: [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ],
        },
        daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        firstDayOfWeek: 0,
        ordinal: function (nth) {
            var s = nth % 100;
            if (s > 3 && s < 21)
                return "th";
            switch (s % 10) {
                case 1:
                    return "st";
                case 2:
                    return "nd";
                case 3:
                    return "rd";
                default:
                    return "th";
            }
        },
        rangeSeparator: " to ",
        weekAbbreviation: "Wk",
        scrollTitle: "Scroll to increment",
        toggleTitle: "Click to toggle",
        amPM: ["AM", "PM"],
        yearAriaLabel: "Year",
        monthAriaLabel: "Month",
        hourAriaLabel: "Hour",
        minuteAriaLabel: "Minute",
        time_24hr: false,
    };

    var fp$d = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Esperanto = {
        firstDayOfWeek: 1,
        rangeSeparator: " ĝis ",
        weekAbbreviation: "Sem",
        scrollTitle: "Rulumu por pligrandigi la valoron",
        toggleTitle: "Klaku por ŝalti",
        weekdays: {
            shorthand: ["Dim", "Lun", "Mar", "Mer", "Ĵaŭ", "Ven", "Sab"],
            longhand: [
                "dimanĉo",
                "lundo",
                "mardo",
                "merkredo",
                "ĵaŭdo",
                "vendredo",
                "sabato",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Maj",
                "Jun",
                "Jul",
                "Aŭg",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "januaro",
                "februaro",
                "marto",
                "aprilo",
                "majo",
                "junio",
                "julio",
                "aŭgusto",
                "septembro",
                "oktobro",
                "novembro",
                "decembro",
            ],
        },
        ordinal: function () {
            return "-a";
        },
        time_24hr: true,
    };
    fp$d.l10ns.eo = Esperanto;
    fp$d.l10ns;

    var fp$e = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Spanish = {
        weekdays: {
            shorthand: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
            longhand: [
                "Domingo",
                "Lunes",
                "Martes",
                "Miércoles",
                "Jueves",
                "Viernes",
                "Sábado",
            ],
        },
        months: {
            shorthand: [
                "Ene",
                "Feb",
                "Mar",
                "Abr",
                "May",
                "Jun",
                "Jul",
                "Ago",
                "Sep",
                "Oct",
                "Nov",
                "Dic",
            ],
            longhand: [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre",
            ],
        },
        ordinal: function () {
            return "º";
        },
        firstDayOfWeek: 1,
        rangeSeparator: " a ",
        time_24hr: true,
    };
    fp$e.l10ns.es = Spanish;
    fp$e.l10ns;

    var fp$f = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Estonian = {
        weekdays: {
            shorthand: ["P", "E", "T", "K", "N", "R", "L"],
            longhand: [
                "Pühapäev",
                "Esmaspäev",
                "Teisipäev",
                "Kolmapäev",
                "Neljapäev",
                "Reede",
                "Laupäev",
            ],
        },
        months: {
            shorthand: [
                "Jaan",
                "Veebr",
                "Märts",
                "Apr",
                "Mai",
                "Juuni",
                "Juuli",
                "Aug",
                "Sept",
                "Okt",
                "Nov",
                "Dets",
            ],
            longhand: [
                "Jaanuar",
                "Veebruar",
                "Märts",
                "Aprill",
                "Mai",
                "Juuni",
                "Juuli",
                "August",
                "September",
                "Oktoober",
                "November",
                "Detsember",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return ".";
        },
        weekAbbreviation: "Näd",
        rangeSeparator: " kuni ",
        scrollTitle: "Keri, et suurendada",
        toggleTitle: "Klõpsa, et vahetada",
        time_24hr: true,
    };
    fp$f.l10ns.et = Estonian;
    fp$f.l10ns;

    var fp$g = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Persian = {
        weekdays: {
            shorthand: ["یک", "دو", "سه", "چهار", "پنج", "جمعه", "شنبه"],
            longhand: [
                "یک‌شنبه",
                "دوشنبه",
                "سه‌شنبه",
                "چهارشنبه",
                "پنچ‌شنبه",
                "جمعه",
                "شنبه",
            ],
        },
        months: {
            shorthand: [
                "ژانویه",
                "فوریه",
                "مارس",
                "آوریل",
                "مه",
                "ژوئن",
                "ژوئیه",
                "اوت",
                "سپتامبر",
                "اکتبر",
                "نوامبر",
                "دسامبر",
            ],
            longhand: [
                "ژانویه",
                "فوریه",
                "مارس",
                "آوریل",
                "مه",
                "ژوئن",
                "ژوئیه",
                "اوت",
                "سپتامبر",
                "اکتبر",
                "نوامبر",
                "دسامبر",
            ],
        },
        firstDayOfWeek: 6,
        ordinal: function () {
            return "";
        },
    };
    fp$g.l10ns.fa = Persian;
    fp$g.l10ns;

    var fp$h = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Finnish = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["su", "ma", "ti", "ke", "to", "pe", "la"],
            longhand: [
                "sunnuntai",
                "maanantai",
                "tiistai",
                "keskiviikko",
                "torstai",
                "perjantai",
                "lauantai",
            ],
        },
        months: {
            shorthand: [
                "tammi",
                "helmi",
                "maalis",
                "huhti",
                "touko",
                "kesä",
                "heinä",
                "elo",
                "syys",
                "loka",
                "marras",
                "joulu",
            ],
            longhand: [
                "tammikuu",
                "helmikuu",
                "maaliskuu",
                "huhtikuu",
                "toukokuu",
                "kesäkuu",
                "heinäkuu",
                "elokuu",
                "syyskuu",
                "lokakuu",
                "marraskuu",
                "joulukuu",
            ],
        },
        ordinal: function () {
            return ".";
        },
        time_24hr: true,
    };
    fp$h.l10ns.fi = Finnish;
    fp$h.l10ns;

    var fp$i = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Faroese = {
        weekdays: {
            shorthand: ["Sun", "Mán", "Týs", "Mik", "Hós", "Frí", "Ley"],
            longhand: [
                "Sunnudagur",
                "Mánadagur",
                "Týsdagur",
                "Mikudagur",
                "Hósdagur",
                "Fríggjadagur",
                "Leygardagur",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mai",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Mars",
                "Apríl",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "Septembur",
                "Oktobur",
                "Novembur",
                "Desembur",
            ],
        },
        ordinal: function () {
            return ".";
        },
        firstDayOfWeek: 1,
        rangeSeparator: " til ",
        weekAbbreviation: "vika",
        scrollTitle: "Rulla fyri at broyta",
        toggleTitle: "Trýst fyri at skifta",
        yearAriaLabel: "Ár",
        time_24hr: true,
    };
    fp$i.l10ns.fo = Faroese;
    fp$i.l10ns;

    var fp$j = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var French = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["dim", "lun", "mar", "mer", "jeu", "ven", "sam"],
            longhand: [
                "dimanche",
                "lundi",
                "mardi",
                "mercredi",
                "jeudi",
                "vendredi",
                "samedi",
            ],
        },
        months: {
            shorthand: [
                "janv",
                "févr",
                "mars",
                "avr",
                "mai",
                "juin",
                "juil",
                "août",
                "sept",
                "oct",
                "nov",
                "déc",
            ],
            longhand: [
                "janvier",
                "février",
                "mars",
                "avril",
                "mai",
                "juin",
                "juillet",
                "août",
                "septembre",
                "octobre",
                "novembre",
                "décembre",
            ],
        },
        ordinal: function (nth) {
            if (nth > 1)
                return "";
            return "er";
        },
        rangeSeparator: " au ",
        weekAbbreviation: "Sem",
        scrollTitle: "Défiler pour augmenter la valeur",
        toggleTitle: "Cliquer pour basculer",
        time_24hr: true,
    };
    fp$j.l10ns.fr = French;
    fp$j.l10ns;

    var fp$k = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Greek = {
        weekdays: {
            shorthand: ["Κυ", "Δε", "Τρ", "Τε", "Πέ", "Πα", "Σά"],
            longhand: [
                "Κυριακή",
                "Δευτέρα",
                "Τρίτη",
                "Τετάρτη",
                "Πέμπτη",
                "Παρασκευή",
                "Σάββατο",
            ],
        },
        months: {
            shorthand: [
                "Ιαν",
                "Φεβ",
                "Μάρ",
                "Απρ",
                "Μάι",
                "Ιούν",
                "Ιούλ",
                "Αύγ",
                "Σεπ",
                "Οκτ",
                "Νοέ",
                "Δεκ",
            ],
            longhand: [
                "Ιανουάριος",
                "Φεβρουάριος",
                "Μάρτιος",
                "Απρίλιος",
                "Μάιος",
                "Ιούνιος",
                "Ιούλιος",
                "Αύγουστος",
                "Σεπτέμβριος",
                "Οκτώβριος",
                "Νοέμβριος",
                "Δεκέμβριος",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        weekAbbreviation: "Εβδ",
        rangeSeparator: " έως ",
        scrollTitle: "Μετακυλήστε για προσαύξηση",
        toggleTitle: "Κάντε κλικ για αλλαγή",
        amPM: ["ΠΜ", "ΜΜ"],
        yearAriaLabel: "χρόνος",
        monthAriaLabel: "μήνας",
        hourAriaLabel: "ώρα",
        minuteAriaLabel: "λεπτό",
    };
    fp$k.l10ns.gr = Greek;
    fp$k.l10ns;

    var fp$l = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Hebrew = {
        weekdays: {
            shorthand: ["א", "ב", "ג", "ד", "ה", "ו", "ש"],
            longhand: ["ראשון", "שני", "שלישי", "רביעי", "חמישי", "שישי", "שבת"],
        },
        months: {
            shorthand: [
                "ינו׳",
                "פבר׳",
                "מרץ",
                "אפר׳",
                "מאי",
                "יוני",
                "יולי",
                "אוג׳",
                "ספט׳",
                "אוק׳",
                "נוב׳",
                "דצמ׳",
            ],
            longhand: [
                "ינואר",
                "פברואר",
                "מרץ",
                "אפריל",
                "מאי",
                "יוני",
                "יולי",
                "אוגוסט",
                "ספטמבר",
                "אוקטובר",
                "נובמבר",
                "דצמבר",
            ],
        },
        rangeSeparator: " אל ",
        time_24hr: true,
    };
    fp$l.l10ns.he = Hebrew;
    fp$l.l10ns;

    var fp$m = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Hindi = {
        weekdays: {
            shorthand: ["रवि", "सोम", "मंगल", "बुध", "गुरु", "शुक्र", "शनि"],
            longhand: [
                "रविवार",
                "सोमवार",
                "मंगलवार",
                "बुधवार",
                "गुरुवार",
                "शुक्रवार",
                "शनिवार",
            ],
        },
        months: {
            shorthand: [
                "जन",
                "फर",
                "मार्च",
                "अप्रेल",
                "मई",
                "जून",
                "जूलाई",
                "अग",
                "सित",
                "अक्ट",
                "नव",
                "दि",
            ],
            longhand: [
                "जनवरी ",
                "फरवरी",
                "मार्च",
                "अप्रेल",
                "मई",
                "जून",
                "जूलाई",
                "अगस्त ",
                "सितम्बर",
                "अक्टूबर",
                "नवम्बर",
                "दिसम्बर",
            ],
        },
    };
    fp$m.l10ns.hi = Hindi;
    fp$m.l10ns;

    var fp$n = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Croatian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["Ned", "Pon", "Uto", "Sri", "Čet", "Pet", "Sub"],
            longhand: [
                "Nedjelja",
                "Ponedjeljak",
                "Utorak",
                "Srijeda",
                "Četvrtak",
                "Petak",
                "Subota",
            ],
        },
        months: {
            shorthand: [
                "Sij",
                "Velj",
                "Ožu",
                "Tra",
                "Svi",
                "Lip",
                "Srp",
                "Kol",
                "Ruj",
                "Lis",
                "Stu",
                "Pro",
            ],
            longhand: [
                "Siječanj",
                "Veljača",
                "Ožujak",
                "Travanj",
                "Svibanj",
                "Lipanj",
                "Srpanj",
                "Kolovoz",
                "Rujan",
                "Listopad",
                "Studeni",
                "Prosinac",
            ],
        },
        time_24hr: true,
    };
    fp$n.l10ns.hr = Croatian;
    fp$n.l10ns;

    var fp$o = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Hungarian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["V", "H", "K", "Sz", "Cs", "P", "Szo"],
            longhand: [
                "Vasárnap",
                "Hétfő",
                "Kedd",
                "Szerda",
                "Csütörtök",
                "Péntek",
                "Szombat",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Már",
                "Ápr",
                "Máj",
                "Jún",
                "Júl",
                "Aug",
                "Szep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Január",
                "Február",
                "Március",
                "Április",
                "Május",
                "Június",
                "Július",
                "Augusztus",
                "Szeptember",
                "Október",
                "November",
                "December",
            ],
        },
        ordinal: function () {
            return ".";
        },
        weekAbbreviation: "Hét",
        scrollTitle: "Görgessen",
        toggleTitle: "Kattintson a váltáshoz",
        rangeSeparator: " - ",
        time_24hr: true,
    };
    fp$o.l10ns.hu = Hungarian;
    fp$o.l10ns;

    var fp$p = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Armenian = {
        weekdays: {
            shorthand: ["Կիր", "Երկ", "Երք", "Չրք", "Հնգ", "Ուրբ", "Շբթ"],
            longhand: [
                "Կիրակի",
                "Եկուշաբթի",
                "Երեքշաբթի",
                "Չորեքշաբթի",
                "Հինգշաբթի",
                "Ուրբաթ",
                "Շաբաթ",
            ],
        },
        months: {
            shorthand: [
                "Հնվ",
                "Փտր",
                "Մար",
                "Ապր",
                "Մայ",
                "Հնս",
                "Հլս",
                "Օգս",
                "Սեպ",
                "Հոկ",
                "Նմբ",
                "Դեկ",
            ],
            longhand: [
                "Հունվար",
                "Փետրվար",
                "Մարտ",
                "Ապրիլ",
                "Մայիս",
                "Հունիս",
                "Հուլիս",
                "Օգոստոս",
                "Սեպտեմբեր",
                "Հոկտեմբեր",
                "Նոյեմբեր",
                "Դեկտեմբեր",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "ՇԲՏ",
        scrollTitle: "Ոլորեք՝ մեծացնելու համար",
        toggleTitle: "Սեղմեք՝ փոխելու համար",
        amPM: ["ՄԿ", "ԿՀ"],
        yearAriaLabel: "Տարի",
        monthAriaLabel: "Ամիս",
        hourAriaLabel: "Ժամ",
        minuteAriaLabel: "Րոպե",
        time_24hr: true,
    };
    fp$p.l10ns.hy = Armenian;
    fp$p.l10ns;

    var fp$q = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Indonesian = {
        weekdays: {
            shorthand: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
            longhand: ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            longhand: [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        time_24hr: true,
        rangeSeparator: " - ",
    };
    fp$q.l10ns.id = Indonesian;
    fp$q.l10ns;

    var fp$r = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Icelandic = {
        weekdays: {
            shorthand: ["Sun", "Mán", "Þri", "Mið", "Fim", "Fös", "Lau"],
            longhand: [
                "Sunnudagur",
                "Mánudagur",
                "Þriðjudagur",
                "Miðvikudagur",
                "Fimmtudagur",
                "Föstudagur",
                "Laugardagur",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Maí",
                "Jún",
                "Júl",
                "Ágú",
                "Sep",
                "Okt",
                "Nóv",
                "Des",
            ],
            longhand: [
                "Janúar",
                "Febrúar",
                "Mars",
                "Apríl",
                "Maí",
                "Júní",
                "Júlí",
                "Ágúst",
                "September",
                "Október",
                "Nóvember",
                "Desember",
            ],
        },
        ordinal: function () {
            return ".";
        },
        firstDayOfWeek: 1,
        rangeSeparator: " til ",
        weekAbbreviation: "vika",
        yearAriaLabel: "Ár",
        time_24hr: true,
    };
    fp$r.l10ns.is = Icelandic;
    fp$r.l10ns;

    var fp$s = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Italian = {
        weekdays: {
            shorthand: ["Dom", "Lun", "Mar", "Mer", "Gio", "Ven", "Sab"],
            longhand: [
                "Domenica",
                "Lunedì",
                "Martedì",
                "Mercoledì",
                "Giovedì",
                "Venerdì",
                "Sabato",
            ],
        },
        months: {
            shorthand: [
                "Gen",
                "Feb",
                "Mar",
                "Apr",
                "Mag",
                "Giu",
                "Lug",
                "Ago",
                "Set",
                "Ott",
                "Nov",
                "Dic",
            ],
            longhand: [
                "Gennaio",
                "Febbraio",
                "Marzo",
                "Aprile",
                "Maggio",
                "Giugno",
                "Luglio",
                "Agosto",
                "Settembre",
                "Ottobre",
                "Novembre",
                "Dicembre",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () { return "°"; },
        rangeSeparator: " al ",
        weekAbbreviation: "Se",
        scrollTitle: "Scrolla per aumentare",
        toggleTitle: "Clicca per cambiare",
        time_24hr: true,
    };
    fp$s.l10ns.it = Italian;
    fp$s.l10ns;

    var fp$t = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Japanese = {
        weekdays: {
            shorthand: ["日", "月", "火", "水", "木", "金", "土"],
            longhand: [
                "日曜日",
                "月曜日",
                "火曜日",
                "水曜日",
                "木曜日",
                "金曜日",
                "土曜日",
            ],
        },
        months: {
            shorthand: [
                "1月",
                "2月",
                "3月",
                "4月",
                "5月",
                "6月",
                "7月",
                "8月",
                "9月",
                "10月",
                "11月",
                "12月",
            ],
            longhand: [
                "1月",
                "2月",
                "3月",
                "4月",
                "5月",
                "6月",
                "7月",
                "8月",
                "9月",
                "10月",
                "11月",
                "12月",
            ],
        },
        time_24hr: true,
        rangeSeparator: " から ",
        monthAriaLabel: "月",
        amPM: ["午前", "午後"],
        yearAriaLabel: "年",
        hourAriaLabel: "時間",
        minuteAriaLabel: "分",
    };
    fp$t.l10ns.ja = Japanese;
    fp$t.l10ns;

    var fp$u = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Georgian = {
        weekdays: {
            shorthand: ["კვ", "ორ", "სა", "ოთ", "ხუ", "პა", "შა"],
            longhand: [
                "კვირა",
                "ორშაბათი",
                "სამშაბათი",
                "ოთხშაბათი",
                "ხუთშაბათი",
                "პარასკევი",
                "შაბათი",
            ],
        },
        months: {
            shorthand: [
                "იან",
                "თებ",
                "მარ",
                "აპრ",
                "მაი",
                "ივნ",
                "ივლ",
                "აგვ",
                "სექ",
                "ოქტ",
                "ნოე",
                "დეკ",
            ],
            longhand: [
                "იანვარი",
                "თებერვალი",
                "მარტი",
                "აპრილი",
                "მაისი",
                "ივნისი",
                "ივლისი",
                "აგვისტო",
                "სექტემბერი",
                "ოქტომბერი",
                "ნოემბერი",
                "დეკემბერი",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "კვ.",
        scrollTitle: "დასქროლეთ გასადიდებლად",
        toggleTitle: "დააკლიკეთ გადართვისთვის",
        amPM: ["AM", "PM"],
        yearAriaLabel: "წელი",
        time_24hr: true,
    };
    fp$u.l10ns.ka = Georgian;
    fp$u.l10ns;

    var fp$v = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Korean = {
        weekdays: {
            shorthand: ["일", "월", "화", "수", "목", "금", "토"],
            longhand: [
                "일요일",
                "월요일",
                "화요일",
                "수요일",
                "목요일",
                "금요일",
                "토요일",
            ],
        },
        months: {
            shorthand: [
                "1월",
                "2월",
                "3월",
                "4월",
                "5월",
                "6월",
                "7월",
                "8월",
                "9월",
                "10월",
                "11월",
                "12월",
            ],
            longhand: [
                "1월",
                "2월",
                "3월",
                "4월",
                "5월",
                "6월",
                "7월",
                "8월",
                "9월",
                "10월",
                "11월",
                "12월",
            ],
        },
        ordinal: function () {
            return "일";
        },
        rangeSeparator: " ~ ",
        amPM: ["오전", "오후"],
    };
    fp$v.l10ns.ko = Korean;
    fp$v.l10ns;

    var fp$w = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Khmer = {
        weekdays: {
            shorthand: ["អាទិត្យ", "ចន្ទ", "អង្គារ", "ពុធ", "ព្រហស.", "សុក្រ", "សៅរ៍"],
            longhand: [
                "អាទិត្យ",
                "ចន្ទ",
                "អង្គារ",
                "ពុធ",
                "ព្រហស្បតិ៍",
                "សុក្រ",
                "សៅរ៍",
            ],
        },
        months: {
            shorthand: [
                "មករា",
                "កុម្ភះ",
                "មីនា",
                "មេសា",
                "ឧសភា",
                "មិថុនា",
                "កក្កដា",
                "សីហា",
                "កញ្ញា",
                "តុលា",
                "វិច្ឆិកា",
                "ធ្នូ",
            ],
            longhand: [
                "មករា",
                "កុម្ភះ",
                "មីនា",
                "មេសា",
                "ឧសភា",
                "មិថុនា",
                "កក្កដា",
                "សីហា",
                "កញ្ញា",
                "តុលា",
                "វិច្ឆិកា",
                "ធ្នូ",
            ],
        },
        ordinal: function () {
            return "";
        },
        firstDayOfWeek: 1,
        rangeSeparator: " ដល់ ",
        weekAbbreviation: "សប្តាហ៍",
        scrollTitle: "រំកិលដើម្បីបង្កើន",
        toggleTitle: "ចុចដើម្បីផ្លាស់ប្ដូរ",
        yearAriaLabel: "ឆ្នាំ",
        time_24hr: true,
    };
    fp$w.l10ns.km = Khmer;
    fp$w.l10ns;

    var fp$x = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Kazakh = {
        weekdays: {
            shorthand: ["Жс", "Дс", "Сc", "Ср", "Бс", "Жм", "Сб"],
            longhand: [
                "Жексенбi",
                "Дүйсенбi",
                "Сейсенбi",
                "Сәрсенбi",
                "Бейсенбi",
                "Жұма",
                "Сенбi",
            ],
        },
        months: {
            shorthand: [
                "Қаң",
                "Ақп",
                "Нау",
                "Сәу",
                "Мам",
                "Мау",
                "Шiл",
                "Там",
                "Қыр",
                "Қаз",
                "Қар",
                "Жел",
            ],
            longhand: [
                "Қаңтар",
                "Ақпан",
                "Наурыз",
                "Сәуiр",
                "Мамыр",
                "Маусым",
                "Шiлде",
                "Тамыз",
                "Қыркүйек",
                "Қазан",
                "Қараша",
                "Желтоқсан",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Апта",
        scrollTitle: "Үлкейту үшін айналдырыңыз",
        toggleTitle: "Ауыстыру үшін басыңыз",
        amPM: ["ТД", "ТК"],
        yearAriaLabel: "Жыл",
    };
    fp$x.l10ns.kz = Kazakh;
    fp$x.l10ns;

    var fp$y = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Lithuanian = {
        weekdays: {
            shorthand: ["S", "Pr", "A", "T", "K", "Pn", "Š"],
            longhand: [
                "Sekmadienis",
                "Pirmadienis",
                "Antradienis",
                "Trečiadienis",
                "Ketvirtadienis",
                "Penktadienis",
                "Šeštadienis",
            ],
        },
        months: {
            shorthand: [
                "Sau",
                "Vas",
                "Kov",
                "Bal",
                "Geg",
                "Bir",
                "Lie",
                "Rgp",
                "Rgs",
                "Spl",
                "Lap",
                "Grd",
            ],
            longhand: [
                "Sausis",
                "Vasaris",
                "Kovas",
                "Balandis",
                "Gegužė",
                "Birželis",
                "Liepa",
                "Rugpjūtis",
                "Rugsėjis",
                "Spalis",
                "Lapkritis",
                "Gruodis",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "-a";
        },
        rangeSeparator: " iki ",
        weekAbbreviation: "Sav",
        scrollTitle: "Keisti laiką pelės rateliu",
        toggleTitle: "Perjungti laiko formatą",
        time_24hr: true,
    };
    fp$y.l10ns.lt = Lithuanian;
    fp$y.l10ns;

    var fp$z = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Latvian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["Sv", "Pr", "Ot", "Tr", "Ce", "Pk", "Se"],
            longhand: [
                "Svētdiena",
                "Pirmdiena",
                "Otrdiena",
                "Trešdiena",
                "Ceturtdiena",
                "Piektdiena",
                "Sestdiena",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mai",
                "Jūn",
                "Jūl",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Janvāris",
                "Februāris",
                "Marts",
                "Aprīlis",
                "Maijs",
                "Jūnijs",
                "Jūlijs",
                "Augusts",
                "Septembris",
                "Oktobris",
                "Novembris",
                "Decembris",
            ],
        },
        rangeSeparator: " līdz ",
        time_24hr: true,
    };
    fp$z.l10ns.lv = Latvian;
    fp$z.l10ns;

    var fp$A = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Macedonian = {
        weekdays: {
            shorthand: ["Не", "По", "Вт", "Ср", "Че", "Пе", "Са"],
            longhand: [
                "Недела",
                "Понеделник",
                "Вторник",
                "Среда",
                "Четврток",
                "Петок",
                "Сабота",
            ],
        },
        months: {
            shorthand: [
                "Јан",
                "Фев",
                "Мар",
                "Апр",
                "Мај",
                "Јун",
                "Јул",
                "Авг",
                "Сеп",
                "Окт",
                "Ное",
                "Дек",
            ],
            longhand: [
                "Јануари",
                "Февруари",
                "Март",
                "Април",
                "Мај",
                "Јуни",
                "Јули",
                "Август",
                "Септември",
                "Октомври",
                "Ноември",
                "Декември",
            ],
        },
        firstDayOfWeek: 1,
        weekAbbreviation: "Нед.",
        rangeSeparator: " до ",
        time_24hr: true,
    };
    fp$A.l10ns.mk = Macedonian;
    fp$A.l10ns;

    var fp$B = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Mongolian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["Да", "Мя", "Лх", "Пү", "Ба", "Бя", "Ня"],
            longhand: ["Даваа", "Мягмар", "Лхагва", "Пүрэв", "Баасан", "Бямба", "Ням"],
        },
        months: {
            shorthand: [
                "1-р сар",
                "2-р сар",
                "3-р сар",
                "4-р сар",
                "5-р сар",
                "6-р сар",
                "7-р сар",
                "8-р сар",
                "9-р сар",
                "10-р сар",
                "11-р сар",
                "12-р сар",
            ],
            longhand: [
                "Нэгдүгээр сар",
                "Хоёрдугаар сар",
                "Гуравдугаар сар",
                "Дөрөвдүгээр сар",
                "Тавдугаар сар",
                "Зургаадугаар сар",
                "Долдугаар сар",
                "Наймдугаар сар",
                "Есдүгээр сар",
                "Аравдугаар сар",
                "Арваннэгдүгээр сар",
                "Арванхоёрдугаар сар",
            ],
        },
        rangeSeparator: "-с ",
        time_24hr: true,
    };
    fp$B.l10ns.mn = Mongolian;
    fp$B.l10ns;

    var fp$C = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Malaysian = {
        weekdays: {
            shorthand: ["Aha", "Isn", "Sel", "Rab", "Kha", "Jum", "Sab"],
            longhand: ["Ahad", "Isnin", "Selasa", "Rabu", "Khamis", "Jumaat", "Sabtu"],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mac",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Ogo",
                "Sep",
                "Okt",
                "Nov",
                "Dis",
            ],
            longhand: [
                "Januari",
                "Februari",
                "Mac",
                "April",
                "Mei",
                "Jun",
                "Julai",
                "Ogos",
                "September",
                "Oktober",
                "November",
                "Disember",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
    };
    fp$C.l10ns;

    var fp$D = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Burmese = {
        weekdays: {
            shorthand: ["နွေ", "လာ", "ဂါ", "ဟူး", "ကြာ", "သော", "နေ"],
            longhand: [
                "တနင်္ဂနွေ",
                "တနင်္လာ",
                "အင်္ဂါ",
                "ဗုဒ္ဓဟူး",
                "ကြာသပတေး",
                "သောကြာ",
                "စနေ",
            ],
        },
        months: {
            shorthand: [
                "ဇန်",
                "ဖေ",
                "မတ်",
                "ပြီ",
                "မေ",
                "ဇွန်",
                "လိုင်",
                "သြ",
                "စက်",
                "အောက်",
                "နို",
                "ဒီ",
            ],
            longhand: [
                "ဇန်နဝါရီ",
                "ဖေဖော်ဝါရီ",
                "မတ်",
                "ဧပြီ",
                "မေ",
                "ဇွန်",
                "ဇူလိုင်",
                "သြဂုတ်",
                "စက်တင်ဘာ",
                "အောက်တိုဘာ",
                "နိုဝင်ဘာ",
                "ဒီဇင်ဘာ",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        time_24hr: true,
    };
    fp$D.l10ns.my = Burmese;
    fp$D.l10ns;

    var fp$E = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Dutch = {
        weekdays: {
            shorthand: ["zo", "ma", "di", "wo", "do", "vr", "za"],
            longhand: [
                "zondag",
                "maandag",
                "dinsdag",
                "woensdag",
                "donderdag",
                "vrijdag",
                "zaterdag",
            ],
        },
        months: {
            shorthand: [
                "jan",
                "feb",
                "mrt",
                "apr",
                "mei",
                "jun",
                "jul",
                "aug",
                "sept",
                "okt",
                "nov",
                "dec",
            ],
            longhand: [
                "januari",
                "februari",
                "maart",
                "april",
                "mei",
                "juni",
                "juli",
                "augustus",
                "september",
                "oktober",
                "november",
                "december",
            ],
        },
        firstDayOfWeek: 1,
        weekAbbreviation: "wk",
        rangeSeparator: " t/m ",
        scrollTitle: "Scroll voor volgende / vorige",
        toggleTitle: "Klik om te wisselen",
        time_24hr: true,
        ordinal: function (nth) {
            if (nth === 1 || nth === 8 || nth >= 20)
                return "ste";
            return "de";
        },
    };
    fp$E.l10ns.nl = Dutch;
    fp$E.l10ns;

    var fp$F = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var NorwegianNynorsk = {
        weekdays: {
            shorthand: ["Sø.", "Må.", "Ty.", "On.", "To.", "Fr.", "La."],
            longhand: [
                "Søndag",
                "Måndag",
                "Tysdag",
                "Onsdag",
                "Torsdag",
                "Fredag",
                "Laurdag",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mars",
                "Apr",
                "Mai",
                "Juni",
                "Juli",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Mars",
                "April",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "September",
                "Oktober",
                "November",
                "Desember",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " til ",
        weekAbbreviation: "Veke",
        scrollTitle: "Scroll for å endre",
        toggleTitle: "Klikk for å veksle",
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$F.l10ns.nn = NorwegianNynorsk;
    fp$F.l10ns;

    var fp$G = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Norwegian = {
        weekdays: {
            shorthand: ["Søn", "Man", "Tir", "Ons", "Tor", "Fre", "Lør"],
            longhand: [
                "Søndag",
                "Mandag",
                "Tirsdag",
                "Onsdag",
                "Torsdag",
                "Fredag",
                "Lørdag",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mai",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Mars",
                "April",
                "Mai",
                "Juni",
                "Juli",
                "August",
                "September",
                "Oktober",
                "November",
                "Desember",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " til ",
        weekAbbreviation: "Uke",
        scrollTitle: "Scroll for å endre",
        toggleTitle: "Klikk for å veksle",
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$G.l10ns.no = Norwegian;
    fp$G.l10ns;

    var fp$H = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Punjabi = {
        weekdays: {
            shorthand: ["ਐਤ", "ਸੋਮ", "ਮੰਗਲ", "ਬੁੱਧ", "ਵੀਰ", "ਸ਼ੁੱਕਰ", "ਸ਼ਨਿੱਚਰ"],
            longhand: [
                "ਐਤਵਾਰ",
                "ਸੋਮਵਾਰ",
                "ਮੰਗਲਵਾਰ",
                "ਬੁੱਧਵਾਰ",
                "ਵੀਰਵਾਰ",
                "ਸ਼ੁੱਕਰਵਾਰ",
                "ਸ਼ਨਿੱਚਰਵਾਰ",
            ],
        },
        months: {
            shorthand: [
                "ਜਨ",
                "ਫ਼ਰ",
                "ਮਾਰ",
                "ਅਪ੍ਰੈ",
                "ਮਈ",
                "ਜੂਨ",
                "ਜੁਲਾ",
                "ਅਗ",
                "ਸਤੰ",
                "ਅਕ",
                "ਨਵੰ",
                "ਦਸੰ",
            ],
            longhand: [
                "ਜਨਵਰੀ",
                "ਫ਼ਰਵਰੀ",
                "ਮਾਰਚ",
                "ਅਪ੍ਰੈਲ",
                "ਮਈ",
                "ਜੂਨ",
                "ਜੁਲਾਈ",
                "ਅਗਸਤ",
                "ਸਤੰਬਰ",
                "ਅਕਤੂਬਰ",
                "ਨਵੰਬਰ",
                "ਦਸੰਬਰ",
            ],
        },
        time_24hr: true,
    };
    fp$H.l10ns.pa = Punjabi;
    fp$H.l10ns;

    var fp$I = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Polish = {
        weekdays: {
            shorthand: ["Nd", "Pn", "Wt", "Śr", "Cz", "Pt", "So"],
            longhand: [
                "Niedziela",
                "Poniedziałek",
                "Wtorek",
                "Środa",
                "Czwartek",
                "Piątek",
                "Sobota",
            ],
        },
        months: {
            shorthand: [
                "Sty",
                "Lut",
                "Mar",
                "Kwi",
                "Maj",
                "Cze",
                "Lip",
                "Sie",
                "Wrz",
                "Paź",
                "Lis",
                "Gru",
            ],
            longhand: [
                "Styczeń",
                "Luty",
                "Marzec",
                "Kwiecień",
                "Maj",
                "Czerwiec",
                "Lipiec",
                "Sierpień",
                "Wrzesień",
                "Październik",
                "Listopad",
                "Grudzień",
            ],
        },
        rangeSeparator: " do ",
        weekAbbreviation: "tydz.",
        scrollTitle: "Przewiń, aby zwiększyć",
        toggleTitle: "Kliknij, aby przełączyć",
        firstDayOfWeek: 1,
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$I.l10ns.pl = Polish;
    fp$I.l10ns;

    var fp$J = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Portuguese = {
        weekdays: {
            shorthand: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
            longhand: [
                "Domingo",
                "Segunda-feira",
                "Terça-feira",
                "Quarta-feira",
                "Quinta-feira",
                "Sexta-feira",
                "Sábado",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Fev",
                "Mar",
                "Abr",
                "Mai",
                "Jun",
                "Jul",
                "Ago",
                "Set",
                "Out",
                "Nov",
                "Dez",
            ],
            longhand: [
                "Janeiro",
                "Fevereiro",
                "Março",
                "Abril",
                "Maio",
                "Junho",
                "Julho",
                "Agosto",
                "Setembro",
                "Outubro",
                "Novembro",
                "Dezembro",
            ],
        },
        rangeSeparator: " até ",
        time_24hr: true,
    };
    fp$J.l10ns.pt = Portuguese;
    fp$J.l10ns;

    var fp$K = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Romanian = {
        weekdays: {
            shorthand: ["Dum", "Lun", "Mar", "Mie", "Joi", "Vin", "Sâm"],
            longhand: [
                "Duminică",
                "Luni",
                "Marți",
                "Miercuri",
                "Joi",
                "Vineri",
                "Sâmbătă",
            ],
        },
        months: {
            shorthand: [
                "Ian",
                "Feb",
                "Mar",
                "Apr",
                "Mai",
                "Iun",
                "Iul",
                "Aug",
                "Sep",
                "Oct",
                "Noi",
                "Dec",
            ],
            longhand: [
                "Ianuarie",
                "Februarie",
                "Martie",
                "Aprilie",
                "Mai",
                "Iunie",
                "Iulie",
                "August",
                "Septembrie",
                "Octombrie",
                "Noiembrie",
                "Decembrie",
            ],
        },
        firstDayOfWeek: 1,
        time_24hr: true,
        ordinal: function () {
            return "";
        },
    };
    fp$K.l10ns.ro = Romanian;
    fp$K.l10ns;

    var fp$L = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Russian = {
        weekdays: {
            shorthand: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            longhand: [
                "Воскресенье",
                "Понедельник",
                "Вторник",
                "Среда",
                "Четверг",
                "Пятница",
                "Суббота",
            ],
        },
        months: {
            shorthand: [
                "Янв",
                "Фев",
                "Март",
                "Апр",
                "Май",
                "Июнь",
                "Июль",
                "Авг",
                "Сен",
                "Окт",
                "Ноя",
                "Дек",
            ],
            longhand: [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Нед.",
        scrollTitle: "Прокрутите для увеличения",
        toggleTitle: "Нажмите для переключения",
        amPM: ["ДП", "ПП"],
        yearAriaLabel: "Год",
        time_24hr: true,
    };
    fp$L.l10ns.ru = Russian;
    fp$L.l10ns;

    var fp$M = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Sinhala = {
        weekdays: {
            shorthand: ["ඉ", "ස", "අ", "බ", "බ්‍ර", "සි", "සෙ"],
            longhand: [
                "ඉරිදා",
                "සඳුදා",
                "අඟහරුවාදා",
                "බදාදා",
                "බ්‍රහස්පතින්දා",
                "සිකුරාදා",
                "සෙනසුරාදා",
            ],
        },
        months: {
            shorthand: [
                "ජන",
                "පෙබ",
                "මාර්",
                "අප්‍රේ",
                "මැයි",
                "ජුනි",
                "ජූලි",
                "අගෝ",
                "සැප්",
                "ඔක්",
                "නොවැ",
                "දෙසැ",
            ],
            longhand: [
                "ජනවාරි",
                "පෙබරවාරි",
                "මාර්තු",
                "අප්‍රේල්",
                "මැයි",
                "ජුනි",
                "ජූලි",
                "අගෝස්තු",
                "සැප්තැම්බර්",
                "ඔක්තෝබර්",
                "නොවැම්බර්",
                "දෙසැම්බර්",
            ],
        },
        time_24hr: true,
    };
    fp$M.l10ns.si = Sinhala;
    fp$M.l10ns;

    var fp$N = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Slovak = {
        weekdays: {
            shorthand: ["Ned", "Pon", "Ut", "Str", "Štv", "Pia", "Sob"],
            longhand: [
                "Nedeľa",
                "Pondelok",
                "Utorok",
                "Streda",
                "Štvrtok",
                "Piatok",
                "Sobota",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Máj",
                "Jún",
                "Júl",
                "Aug",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Január",
                "Február",
                "Marec",
                "Apríl",
                "Máj",
                "Jún",
                "Júl",
                "August",
                "September",
                "Október",
                "November",
                "December",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " do ",
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$N.l10ns.sk = Slovak;
    fp$N.l10ns;

    var fp$O = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Slovenian = {
        weekdays: {
            shorthand: ["Ned", "Pon", "Tor", "Sre", "Čet", "Pet", "Sob"],
            longhand: [
                "Nedelja",
                "Ponedeljek",
                "Torek",
                "Sreda",
                "Četrtek",
                "Petek",
                "Sobota",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Maj",
                "Jun",
                "Jul",
                "Avg",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Marec",
                "April",
                "Maj",
                "Junij",
                "Julij",
                "Avgust",
                "September",
                "Oktober",
                "November",
                "December",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " do ",
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$O.l10ns.sl = Slovenian;
    fp$O.l10ns;

    var fp$P = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Albanian = {
        weekdays: {
            shorthand: ["Di", "Hë", "Ma", "Më", "En", "Pr", "Sh"],
            longhand: [
                "E Diel",
                "E Hënë",
                "E Martë",
                "E Mërkurë",
                "E Enjte",
                "E Premte",
                "E Shtunë",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Shk",
                "Mar",
                "Pri",
                "Maj",
                "Qer",
                "Kor",
                "Gus",
                "Sht",
                "Tet",
                "Nën",
                "Dhj",
            ],
            longhand: [
                "Janar",
                "Shkurt",
                "Mars",
                "Prill",
                "Maj",
                "Qershor",
                "Korrik",
                "Gusht",
                "Shtator",
                "Tetor",
                "Nëntor",
                "Dhjetor",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " deri ",
        weekAbbreviation: "Java",
        yearAriaLabel: "Viti",
        monthAriaLabel: "Muaji",
        hourAriaLabel: "Ora",
        minuteAriaLabel: "Minuta",
        time_24hr: true,
    };
    fp$P.l10ns.sq = Albanian;
    fp$P.l10ns;

    var fp$Q = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Serbian = {
        weekdays: {
            shorthand: ["Ned", "Pon", "Uto", "Sre", "Čet", "Pet", "Sub"],
            longhand: [
                "Nedelja",
                "Ponedeljak",
                "Utorak",
                "Sreda",
                "Četvrtak",
                "Petak",
                "Subota",
            ],
        },
        months: {
            shorthand: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Maj",
                "Jun",
                "Jul",
                "Avg",
                "Sep",
                "Okt",
                "Nov",
                "Dec",
            ],
            longhand: [
                "Januar",
                "Februar",
                "Mart",
                "April",
                "Maj",
                "Jun",
                "Jul",
                "Avgust",
                "Septembar",
                "Oktobar",
                "Novembar",
                "Decembar",
            ],
        },
        firstDayOfWeek: 1,
        weekAbbreviation: "Ned.",
        rangeSeparator: " do ",
        time_24hr: true,
    };
    fp$Q.l10ns.sr = Serbian;
    fp$Q.l10ns;

    var fp$R = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Swedish = {
        firstDayOfWeek: 1,
        weekAbbreviation: "v",
        weekdays: {
            shorthand: ["sön", "mån", "tis", "ons", "tor", "fre", "lör"],
            longhand: [
                "söndag",
                "måndag",
                "tisdag",
                "onsdag",
                "torsdag",
                "fredag",
                "lördag",
            ],
        },
        months: {
            shorthand: [
                "jan",
                "feb",
                "mar",
                "apr",
                "maj",
                "jun",
                "jul",
                "aug",
                "sep",
                "okt",
                "nov",
                "dec",
            ],
            longhand: [
                "januari",
                "februari",
                "mars",
                "april",
                "maj",
                "juni",
                "juli",
                "augusti",
                "september",
                "oktober",
                "november",
                "december",
            ],
        },
        rangeSeparator: " till ",
        time_24hr: true,
        ordinal: function () {
            return ".";
        },
    };
    fp$R.l10ns.sv = Swedish;
    fp$R.l10ns;

    var fp$S = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Thai = {
        weekdays: {
            shorthand: ["อา", "จ", "อ", "พ", "พฤ", "ศ", "ส"],
            longhand: [
                "อาทิตย์",
                "จันทร์",
                "อังคาร",
                "พุธ",
                "พฤหัสบดี",
                "ศุกร์",
                "เสาร์",
            ],
        },
        months: {
            shorthand: [
                "ม.ค.",
                "ก.พ.",
                "มี.ค.",
                "เม.ย.",
                "พ.ค.",
                "มิ.ย.",
                "ก.ค.",
                "ส.ค.",
                "ก.ย.",
                "ต.ค.",
                "พ.ย.",
                "ธ.ค.",
            ],
            longhand: [
                "มกราคม",
                "กุมภาพันธ์",
                "มีนาคม",
                "เมษายน",
                "พฤษภาคม",
                "มิถุนายน",
                "กรกฎาคม",
                "สิงหาคม",
                "กันยายน",
                "ตุลาคม",
                "พฤศจิกายน",
                "ธันวาคม",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " ถึง ",
        scrollTitle: "เลื่อนเพื่อเพิ่มหรือลด",
        toggleTitle: "คลิกเพื่อเปลี่ยน",
        time_24hr: true,
        ordinal: function () {
            return "";
        },
    };
    fp$S.l10ns.th = Thai;
    fp$S.l10ns;

    var fp$T = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Turkish = {
        weekdays: {
            shorthand: ["Paz", "Pzt", "Sal", "Çar", "Per", "Cum", "Cmt"],
            longhand: [
                "Pazar",
                "Pazartesi",
                "Salı",
                "Çarşamba",
                "Perşembe",
                "Cuma",
                "Cumartesi",
            ],
        },
        months: {
            shorthand: [
                "Oca",
                "Şub",
                "Mar",
                "Nis",
                "May",
                "Haz",
                "Tem",
                "Ağu",
                "Eyl",
                "Eki",
                "Kas",
                "Ara",
            ],
            longhand: [
                "Ocak",
                "Şubat",
                "Mart",
                "Nisan",
                "Mayıs",
                "Haziran",
                "Temmuz",
                "Ağustos",
                "Eylül",
                "Ekim",
                "Kasım",
                "Aralık",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return ".";
        },
        rangeSeparator: " - ",
        weekAbbreviation: "Hf",
        scrollTitle: "Artırmak için kaydırın",
        toggleTitle: "Aç/Kapa",
        amPM: ["ÖÖ", "ÖS"],
        time_24hr: true,
    };
    fp$T.l10ns.tr = Turkish;
    fp$T.l10ns;

    var fp$U = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Ukrainian = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            longhand: [
                "Неділя",
                "Понеділок",
                "Вівторок",
                "Середа",
                "Четвер",
                "П'ятниця",
                "Субота",
            ],
        },
        months: {
            shorthand: [
                "Січ",
                "Лют",
                "Бер",
                "Кві",
                "Тра",
                "Чер",
                "Лип",
                "Сер",
                "Вер",
                "Жов",
                "Лис",
                "Гру",
            ],
            longhand: [
                "Січень",
                "Лютий",
                "Березень",
                "Квітень",
                "Травень",
                "Червень",
                "Липень",
                "Серпень",
                "Вересень",
                "Жовтень",
                "Листопад",
                "Грудень",
            ],
        },
        time_24hr: true,
    };
    fp$U.l10ns.uk = Ukrainian;
    fp$U.l10ns;

    var fp$V = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Uzbek = {
        weekdays: {
            shorthand: ["Якш", "Душ", "Сеш", "Чор", "Пай", "Жум", "Шан"],
            longhand: [
                "Якшанба",
                "Душанба",
                "Сешанба",
                "Чоршанба",
                "Пайшанба",
                "Жума",
                "Шанба",
            ],
        },
        months: {
            shorthand: [
                "Янв",
                "Фев",
                "Мар",
                "Апр",
                "Май",
                "Июн",
                "Июл",
                "Авг",
                "Сен",
                "Окт",
                "Ноя",
                "Дек",
            ],
            longhand: [
                "Январ",
                "Феврал",
                "Март",
                "Апрел",
                "Май",
                "Июн",
                "Июл",
                "Август",
                "Сентябр",
                "Октябр",
                "Ноябр",
                "Декабр",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Ҳафта",
        scrollTitle: "Катталаштириш учун айлантиринг",
        toggleTitle: "Ўтиш учун босинг",
        amPM: ["AM", "PM"],
        yearAriaLabel: "Йил",
        time_24hr: true,
    };
    fp$V.l10ns.uz = Uzbek;
    fp$V.l10ns;

    var fp$W = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var UzbekLatin = {
        weekdays: {
            shorthand: ["Ya", "Du", "Se", "Cho", "Pa", "Ju", "Sha"],
            longhand: [
                "Yakshanba",
                "Dushanba",
                "Seshanba",
                "Chorshanba",
                "Payshanba",
                "Juma",
                "Shanba",
            ],
        },
        months: {
            shorthand: [
                "Yan",
                "Fev",
                "Mar",
                "Apr",
                "May",
                "Iyun",
                "Iyul",
                "Avg",
                "Sen",
                "Okt",
                "Noy",
                "Dek",
            ],
            longhand: [
                "Yanvar",
                "Fevral",
                "Mart",
                "Aprel",
                "May",
                "Iyun",
                "Iyul",
                "Avgust",
                "Sentabr",
                "Oktabr",
                "Noyabr",
                "Dekabr",
            ],
        },
        firstDayOfWeek: 1,
        ordinal: function () {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Hafta",
        scrollTitle: "Kattalashtirish uchun aylantiring",
        toggleTitle: "O‘tish uchun bosing",
        amPM: ["AM", "PM"],
        yearAriaLabel: "Yil",
        time_24hr: true,
    };
    fp$W.l10ns["uz_latn"] = UzbekLatin;
    fp$W.l10ns;

    var fp$X = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Vietnamese = {
        weekdays: {
            shorthand: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
            longhand: [
                "Chủ nhật",
                "Thứ hai",
                "Thứ ba",
                "Thứ tư",
                "Thứ năm",
                "Thứ sáu",
                "Thứ bảy",
            ],
        },
        months: {
            shorthand: [
                "Th1",
                "Th2",
                "Th3",
                "Th4",
                "Th5",
                "Th6",
                "Th7",
                "Th8",
                "Th9",
                "Th10",
                "Th11",
                "Th12",
            ],
            longhand: [
                "Tháng một",
                "Tháng hai",
                "Tháng ba",
                "Tháng tư",
                "Tháng năm",
                "Tháng sáu",
                "Tháng bảy",
                "Tháng tám",
                "Tháng chín",
                "Tháng mười",
                "Tháng mười một",
                "Tháng mười hai",
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: " đến ",
    };
    fp$X.l10ns.vn = Vietnamese;
    fp$X.l10ns;

    var fp$Y = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var Mandarin = {
        weekdays: {
            shorthand: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
            longhand: [
                "星期日",
                "星期一",
                "星期二",
                "星期三",
                "星期四",
                "星期五",
                "星期六",
            ],
        },
        months: {
            shorthand: [
                "一月",
                "二月",
                "三月",
                "四月",
                "五月",
                "六月",
                "七月",
                "八月",
                "九月",
                "十月",
                "十一月",
                "十二月",
            ],
            longhand: [
                "一月",
                "二月",
                "三月",
                "四月",
                "五月",
                "六月",
                "七月",
                "八月",
                "九月",
                "十月",
                "十一月",
                "十二月",
            ],
        },
        rangeSeparator: " 至 ",
        weekAbbreviation: "周",
        scrollTitle: "滚动切换",
        toggleTitle: "点击切换 12/24 小时时制",
    };
    fp$Y.l10ns.zh = Mandarin;
    fp$Y.l10ns;

    var fp$Z = typeof window !== "undefined" && window.flatpickr !== undefined
        ? window.flatpickr
        : {
            l10ns: {},
        };
    var MandarinTraditional = {
        weekdays: {
            shorthand: ["週日", "週一", "週二", "週三", "週四", "週五", "週六"],
            longhand: [
                "星期日",
                "星期一",
                "星期二",
                "星期三",
                "星期四",
                "星期五",
                "星期六",
            ],
        },
        months: {
            shorthand: [
                "一月",
                "二月",
                "三月",
                "四月",
                "五月",
                "六月",
                "七月",
                "八月",
                "九月",
                "十月",
                "十一月",
                "十二月",
            ],
            longhand: [
                "一月",
                "二月",
                "三月",
                "四月",
                "五月",
                "六月",
                "七月",
                "八月",
                "九月",
                "十月",
                "十一月",
                "十二月",
            ],
        },
        rangeSeparator: " 至 ",
        weekAbbreviation: "週",
        scrollTitle: "滾動切換",
        toggleTitle: "點擊切換 12/24 小時時制",
    };
    fp$Z.l10ns.zh_tw = MandarinTraditional;
    fp$Z.l10ns;

    var l10n = {
        ar: Arabic,
        at: Austria,
        az: Azerbaijan,
        be: Belarusian,
        bg: Bulgarian,
        bn: Bangla,
        bs: Bosnian,
        ca: Catalan,
        ckb: Kurdish,
        cat: Catalan,
        cs: Czech,
        cy: Welsh,
        da: Danish,
        de: German,
        default: __assign({}, english),
        en: english,
        eo: Esperanto,
        es: Spanish,
        et: Estonian,
        fa: Persian,
        fi: Finnish,
        fo: Faroese,
        fr: French,
        gr: Greek,
        he: Hebrew,
        hi: Hindi,
        hr: Croatian,
        hu: Hungarian,
        hy: Armenian,
        id: Indonesian,
        is: Icelandic,
        it: Italian,
        ja: Japanese,
        ka: Georgian,
        ko: Korean,
        km: Khmer,
        kz: Kazakh,
        lt: Lithuanian,
        lv: Latvian,
        mk: Macedonian,
        mn: Mongolian,
        ms: Malaysian,
        my: Burmese,
        nl: Dutch,
        nn: NorwegianNynorsk,
        no: Norwegian,
        pa: Punjabi,
        pl: Polish,
        pt: Portuguese,
        ro: Romanian,
        ru: Russian,
        si: Sinhala,
        sk: Slovak,
        sl: Slovenian,
        sq: Albanian,
        sr: Serbian,
        sv: Swedish,
        th: Thai,
        tr: Turkish,
        uk: Ukrainian,
        vn: Vietnamese,
        zh: Mandarin,
        zh_tw: MandarinTraditional,
        uz: Uzbek,
        uz_latn: UzbekLatin,
    };

    exports.default = l10n;

    Object.defineProperty(exports, '__esModule', { value: true });

})));


/***/ }),

/***/ "./node_modules/jsrender/jsrender.js":
/*!*******************************************!*\
  !*** ./node_modules/jsrender/jsrender.js ***!
  \*******************************************/
/***/ ((module) => {

/*! JsRender v1.0.15: http://jsviews.com/#jsrender */
/*! **VERSION FOR WEB** (For NODE.JS see http://jsviews.com/download/jsrender-node.js) */
/*
 * Best-of-breed templating in browser or on Node.js.
 * Does not require jQuery, or HTML DOM
 * Integrates with JsViews (http://jsviews.com/#jsviews)
 *
 * Copyright 2024, Boris Moore
 * Released under the MIT License.
 */

//jshint -W018, -W041, -W120

(function(factory, global) {
	// global var is the this object, which is window when running in the usual browser environment
	var $ = global.jQuery;

	if (true) { // CommonJS e.g. Browserify
		module.exports = $
			? factory(global, $)
			: function($) { // If no global jQuery, take optional jQuery passed as parameter: require('jsrender')(jQuery)
				if ($ && !$.fn) {
					throw "Provide jQuery or null";
				}
				return factory(global, $);
			};
	} else {}
} (

// factory (for jsrender.js)
function(global, $) {
"use strict";

//========================== Top-level vars ==========================

// global var is the this object, which is window when running in the usual browser environment
var setGlobals = $ === false; // Only set globals if script block in browser (not AMD and not CommonJS)

$ = $ && $.fn ? $ : global.jQuery; // $ is jQuery passed in by CommonJS loader (Browserify), or global jQuery.

var versionNumber = "v1.0.15",
	jsvStoreName, rTag, rTmplString, topView, $views, $expando,
	_ocp = "_ocp",      // Observable contextual parameter

	$isFunction, $isArray, $templates, $converters, $helpers, $tags, $sub, $subSettings, $subSettingsAdvanced, $viewsSettings,
	delimOpenChar0, delimOpenChar1, delimCloseChar0, delimCloseChar1, linkChar, setting, baseOnError,

	isRenderCall,
	rNewLine = /[ \t]*(\r\n|\n|\r)/g,
	rUnescapeQuotes = /\\(['"\\])/g, // Unescape quotes and trim
	rEscapeQuotes = /['"\\]/g, // Escape quotes and \ character
	rBuildHash = /(?:\x08|^)(onerror:)?(?:(~?)(([\w$.]+):)?([^\x08]+))\x08(,)?([^\x08]+)/gi,
	rTestElseIf = /^if\s/,
	rFirstElem = /<(\w+)[>\s]/,
	rAttrEncode = /[\x00`><"'&=]/g, // Includes > encoding since rConvertMarkers in JsViews does not skip > characters in attribute strings
	rIsHtml = /[\x00`><\"'&=]/,
	rHasHandlers = /^on[A-Z]|^convert(Back)?$/,
	rWrappedInViewMarker = /^\#\d+_`[\s\S]*\/\d+_`$/,
	rHtmlEncode = rAttrEncode,
	rDataEncode = /[&<>]/g,
	rDataUnencode = /&(amp|gt|lt);/g,
	rBracketQuote = /\[['"]?|['"]?\]/g,
	viewId = 0,
	charEntities = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		"\x00": "&#0;",
		"'": "&#39;",
		'"': "&#34;",
		"`": "&#96;",
		"=": "&#61;"
	},
	charsFromEntities = {
		amp: "&",
		gt: ">",
		lt: "<"
	},
	HTML = "html",
	STRING = "string",
	OBJECT = "object",
	tmplAttr = "data-jsv-tmpl",
	jsvTmpl = "jsvTmpl",
	indexStr = "For #index in nested block use #getIndex().",
	cpFnStore = {},     // Compiled furnctions for computed values in template expressions (properties, methods, helpers)
	$render = {},

	jsr = global.jsrender,
	jsrToJq = jsr && $ && !$.render, // JsRender already loaded, without jQuery. but we will re-load it now to attach to jQuery

	jsvStores = {
		template: {
			compile: compileTmpl
		},
		tag: {
			compile: compileTag
		},
		viewModel: {
			compile: compileViewModel
		},
		helper: {},
		converter: {}
	};

	// views object ($.views if jQuery is loaded, jsrender.views if no jQuery, e.g. in Node.js)
	$views = {
		jsviews: versionNumber,
		sub: {
			// subscription, e.g. JsViews integration
			rPath: /^(!*?)(?:null|true|false|\d[\d.]*|([\w$]+|\.|~([\w$]+)|#(view|([\w$]+))?)([\w$.^]*?)(?:[.[^]([\w$]+)\]?)?)$/g,
			//        not                               object     helper    view  viewProperty pathTokens      leafToken

			rPrm: /(\()(?=\s*\()|(?:([([])\s*)?(?:(\^?)(~?[\w$.^]+)?\s*((\+\+|--)|\+|-|~(?![\w$])|&&|\|\||===|!==|==|!=|<=|>=|[<>%*:?\/]|(=))\s*|(!*?(@)?[#~]?[\w$.^]+)([([])?)|(,\s*)|(?:(\()\s*)?\\?(?:(')|("))|(?:\s*(([)\]])(?=[.^]|\s*$|[^([])|[)\]])([([]?))|(\s+)/g,
			//   lftPrn0           lftPrn         bound     path               operator     err                                          eq      path2 late            prn      comma  lftPrn2          apos quot        rtPrn  rtPrnDot                  prn2     space

			View: View,
			Err: JsViewsError,
			tmplFn: tmplFn,
			parse: parseParams,
			extend: $extend,
			extendCtx: extendCtx,
			syntaxErr: syntaxError,
			onStore: {
				template: function(name, item) {
					if (item === null) {
						delete $render[name];
					} else if (name) {
						$render[name] = item;
					}
				}
			},
			addSetting: addSetting,
			settings: {
				allowCode: false
			},
			advSet: noop, // Update advanced settings
			_thp: tagHandlersFromProps,
			_gm: getMethod,
			_tg: function() {}, // Constructor for tagDef
			_cnvt: convertVal,
			_tag: renderTag,
			_er: error,
			_err: onRenderError,
			_cp: retVal, // Get observable contextual parameters (or properties) ~foo=expr. In JsRender, simply returns val.
			_sq: function(token) {
				if (token === "constructor") {
					syntaxError("");
				}
				return token;
			}
		},
		settings: {
			delimiters: $viewsDelimiters,
			advanced: function(value) {
				return value
					? (
							$extend($subSettingsAdvanced, value),
							$sub.advSet(),
							$viewsSettings
						)
						: $subSettingsAdvanced;
				}
		},
		map: dataMap // If jsObservable loaded first, use that definition of dataMap
	};

function getDerivedMethod(baseMethod, method) {
	return function() {
		var ret,
			tag = this,
			prevBase = tag.base;

		tag.base = baseMethod; // Within method call, calling this.base will call the base method
		ret = method.apply(tag, arguments); // Call the method
		tag.base = prevBase; // Replace this.base to be the base method of the previous call, for chained calls
		return ret;
	};
}

function getMethod(baseMethod, method) {
	// For derived methods (or handlers declared declaratively as in {{:foo onChange=~fooChanged}} replace by a derived method, to allow using this.base(...)
	// or this.baseApply(arguments) to call the base implementation. (Equivalent to this._super(...) and this._superApply(arguments) in jQuery UI)
	if ($isFunction(method)) {
		method = getDerivedMethod(
				!baseMethod
					? noop // no base method implementation, so use noop as base method
					: baseMethod._d
						? baseMethod // baseMethod is a derived method, so use it
						: getDerivedMethod(noop, baseMethod), // baseMethod is not derived so make its base method be the noop method
				method
			);
		method._d = (baseMethod && baseMethod._d || 0) + 1; // Add flag for derived method (incremented for derived of derived...)
	}
	return method;
}

function tagHandlersFromProps(tag, tagCtx) {
	var prop,
		props = tagCtx.props;
	for (prop in props) {
		if (rHasHandlers.test(prop) && !(tag[prop] && tag[prop].fix)) { // Don't override handlers with fix expando (used in datepicker and spinner)
			tag[prop] = prop !== "convert" ? getMethod(tag.constructor.prototype[prop], props[prop]) : props[prop];
			// Copy over the onFoo props, convert and convertBack from tagCtx.props to tag (overrides values in tagDef).
			// Note: unsupported scenario: if handlers are dynamically added ^onFoo=expression this will work, but dynamically removing will not work.
		}
	}
}

function retVal(val) {
	return val;
}

function noop() {
	return "";
}

function dbgBreak(val) {
	// Usage examples: {{dbg:...}}, {{:~dbg(...)}}, {{dbg .../}}, {^{for ... onAfterLink=~dbg}} etc.
	try {
		console.log("JsRender dbg breakpoint: " + val);
		throw "dbg breakpoint"; // To break here, stop on caught exceptions.
	}
	catch (e) {}
	return this.base ? this.baseApply(arguments) : val;
}

function JsViewsError(message) {
	// Error exception type for JsViews/JsRender
	// Override of $.views.sub.Error is possible
	this.name = ($.link ? "JsViews" : "JsRender") + " Error";
	this.message = message || this.name;
}

function $extend(target, source) {
	if (target) {
		for (var name in source) {
			target[name] = source[name];
		}
		return target;
	}
}

(JsViewsError.prototype = new Error()).constructor = JsViewsError;

//========================== Top-level functions ==========================

//===================
// views.delimiters
//===================

	/**
	* Set the tag opening and closing delimiters and 'link' character. Default is "{{", "}}" and "^"
	* openChars, closeChars: opening and closing strings, each with two characters
	* $.views.settings.delimiters(...)
	*
	* @param {string}   openChars
	* @param {string}   [closeChars]
	* @param {string}   [link]
	* @returns {Settings}
	*
	* Get delimiters
	* delimsArray = $.views.settings.delimiters()
	*
	* @returns {string[]}
	*/
function $viewsDelimiters(openChars, closeChars, link) {
	if (!openChars) {
		return $subSettings.delimiters;
	}
	if ($isArray(openChars)) {
		return $viewsDelimiters.apply($views, openChars);
	}
	linkChar = link ? link[0] : linkChar;
	if (!/^(\W|_){5}$/.test(openChars + closeChars + linkChar)) {
		error("Invalid delimiters"); // Must be non-word characters, and openChars and closeChars must each be length 2
	}
	delimOpenChar0 = openChars[0];
	delimOpenChar1 = openChars[1];
	delimCloseChar0 = closeChars[0];
	delimCloseChar1 = closeChars[1];

	$subSettings.delimiters = [delimOpenChar0 + delimOpenChar1, delimCloseChar0 + delimCloseChar1, linkChar];

	// Escape the characters - since they could be regex special characters
	openChars = "\\" + delimOpenChar0 + "(\\" + linkChar + ")?\\" + delimOpenChar1; // Default is "{^{"
	closeChars = "\\" + delimCloseChar0 + "\\" + delimCloseChar1;                   // Default is "}}"
	// Build regex with new delimiters
	//          [tag    (followed by / space or })  or cvtr+colon or html or code] followed by space+params then convertBack?
	rTag = "(?:(\\w+(?=[\\/\\s\\" + delimCloseChar0 + "]))|(\\w+)?(:)|(>)|(\\*))\\s*((?:[^\\"
		+ delimCloseChar0 + "]|\\" + delimCloseChar0 + "(?!\\" + delimCloseChar1 + "))*?)";

	// Make rTag available to JsViews (or other components) for parsing binding expressions
	$sub.rTag = "(?:" + rTag + ")";
	//                        { ^? {   tag+params slash?  or closingTag                                                   or comment
	rTag = new RegExp("(?:" + openChars + rTag + "(\\/)?|\\" + delimOpenChar0 + "(\\" + linkChar + ")?\\" + delimOpenChar1 + "(?:(?:\\/(\\w+))\\s*|!--[\\s\\S]*?--))" + closeChars, "g");

	// Default:  bind     tagName         cvt   cln html code    params            slash   bind2         closeBlk  comment
	//      /(?:{(\^)?{(?:(\w+(?=[\/\s}]))|(\w+)?(:)|(>)|(\*))\s*((?:[^}]|}(?!}))*?)(\/)?|{(\^)?{(?:(?:\/(\w+))\s*|!--[\s\S]*?--))}}

	$sub.rTmpl = new RegExp("^\\s|\\s$|<.*>|([^\\\\]|^)[{}]|" + openChars + ".*" + closeChars);
	// $sub.rTmpl looks for initial or final white space, html tags or { or } char not preceded by \\, or JsRender tags {{xxx}}.
	// Each of these strings are considered NOT to be jQuery selectors
	return $viewsSettings;
}

//=========
// View.get
//=========

function getView(inner, type) { //view.get(inner, type)
	if (!type && inner !== true) {
		// view.get(type)
		type = inner;
		inner = undefined;
	}

	var views, i, l, found,
		view = this,
		root = type === "root";
		// view.get("root") returns view.root, view.get() returns view.parent, view.get(true) returns view.views[0].

	if (inner) {
		// Go through views - this one, and all nested ones, depth-first - and return first one with given type.
		// If type is undefined, i.e. view.get(true), return first child view.
		found = type && view.type === type && view;
		if (!found) {
			views = view.views;
			if (view._.useKey) {
				for (i in views) {
					if (found = type ? views[i].get(inner, type) : views[i]) {
						break;
					}
				}
			} else {
				for (i = 0, l = views.length; !found && i < l; i++) {
					found = type ? views[i].get(inner, type) : views[i];
				}
			}
		}
	} else if (root) {
		// Find root view. (view whose parent is top view)
		found = view.root;
	} else if (type) {
		while (view && !found) {
			// Go through views - this one, and all parent ones - and return first one with given type.
			found = view.type === type ? view : undefined;
			view = view.parent;
		}
	} else {
		found = view.parent;
	}
	return found || undefined;
}

function getNestedIndex() {
	var view = this.get("item");
	return view ? view.index : undefined;
}

getNestedIndex.depends = function() {
	return [this.get("item"), "index"];
};

function getIndex() {
	return this.index;
}

getIndex.depends = "index";

//==================
// View.ctxPrm, etc.
//==================

/* Internal private: view._getOb() */
function getPathObject(ob, path, ltOb, fn) {
	// Iterate through path to late paths: @a.b.c paths
	// Return "" (or noop if leaf is a function @a.b.c(...) ) if intermediate object not yet available
	var prevOb, tokens, l,
		i = 0;
	if (ltOb === 1) {
		fn = 1;
		ltOb = undefined;
	}
	// Paths like ^a^b^c or ~^a^b^c will not throw if an object in path is undefined.
	if (path) {
		tokens = path.split(".");
		l = tokens.length;

		for (; ob && i < l; i++) {
			prevOb = ob;
			ob = tokens[i] ? ob[tokens[i]] : ob;
		}
	}
	if (ltOb) {
		ltOb.lt = ltOb.lt || i<l; // If i < l there was an object in the path not yet available
	}
	return ob === undefined
		? fn ? noop : ""
		: fn ? function() {
			return ob.apply(prevOb, arguments);
		} : ob;
}

function contextParameter(key, value, get) {
	// Helper method called as view.ctxPrm(key) for helpers or template parameters ~foo - from compiled template or from context callback
	var wrapped, deps, res, obsCtxPrm, tagElse, callView, newRes,
		storeView = this,
		isUpdate = !isRenderCall && arguments.length > 1,
		store = storeView.ctx;
	if (key) {
		if (!storeView._) { // tagCtx.ctxPrm() call
			tagElse = storeView.index;
			storeView = storeView.tag;
		}
		callView = storeView;
		if (store && store.hasOwnProperty(key) || (store = $helpers).hasOwnProperty(key)) {
			res = store[key];
			if (key === "tag" || key === "tagCtx" || key === "root" || key === "parentTags") {
				return res;
			}
		} else {
			store = undefined;
		}
		if (!isRenderCall && storeView.tagCtx || storeView.linked) { // Data-linked view, or tag instance
			if (!res || !res._cxp) {
				// Not a contextual parameter
				// Set storeView to tag (if this is a tag.ctxPrm() call) or to root view ("data" view of linked template)
				storeView = storeView.tagCtx || $isFunction(res)
					? storeView // Is a tag, not a view, or is a computed contextual parameter, so scope to the callView, not the 'scope view'
					: (storeView = storeView.scope || storeView,
						!storeView.isTop && storeView.ctx.tag // If this view is in a tag, set storeView to the tag
							|| storeView);
				if (res !== undefined && storeView.tagCtx) {
					// If storeView is a tag, but the contextual parameter has been set at at higher level (e.g. helpers)...
					storeView = storeView.tagCtx.view.scope; // then move storeView to the outer level (scope of tag container view)
				}
				store = storeView._ocps;
				res = store && store.hasOwnProperty(key) && store[key] || res;
				if (!(res && res._cxp) && (get || isUpdate)) {
					// Create observable contextual parameter
					(store || (storeView._ocps = storeView._ocps || {}))[key]
						= res
						= [{
							_ocp: res, // The observable contextual parameter value
							_vw: callView,
							_key: key
						}];
					res._cxp = {
						path: _ocp,
						ind: 0,
						updateValue: function(val, path) {
							$.observable(res[0]).setProperty(_ocp, val); // Set the value (res[0]._ocp)
							return this;
						}
					};
				}
			}
			if (obsCtxPrm = res && res._cxp) {
				// If this helper resource is an observable contextual parameter
				if (arguments.length > 2) {
					deps = res[1] ? $sub._ceo(res[1].deps) : [_ocp]; // fn deps (with any exprObs cloned using $sub._ceo)
					deps.unshift(res[0]); // view
					deps._cxp = obsCtxPrm;
					// In a context callback for a contextual param, we set get = true, to get ctxPrm [view, dependencies...] array - needed for observe call
					return deps;
				}
				tagElse = obsCtxPrm.tagElse;
				newRes = res[1] // linkFn for compiled expression
					? obsCtxPrm.tag && obsCtxPrm.tag.cvtArgs
						? obsCtxPrm.tag.cvtArgs(tagElse, 1)[obsCtxPrm.ind] // = tag.bndArgs() - for tag contextual parameter
						: res[1](res[0].data, res[0], $sub) // = fn(data, view, $sub) for compiled binding expression
					: res[0]._ocp; // Observable contextual parameter (uninitialized, or initialized as static expression, so no path dependencies)
				if (isUpdate) {
					$sub._ucp(key, value, storeView, obsCtxPrm); // Update observable contextual parameter
					return storeView;
				}
				res = newRes;
			}
		}
		if (res && $isFunction(res)) {
			// If a helper is of type function we will wrap it, so if called with no this pointer it will be called with the
			// view as 'this' context. If the helper ~foo() was in a data-link expression, the view will have a 'temporary' linkCtx property too.
			// Note that helper functions on deeper paths will have specific this pointers, from the preceding path.
			// For example, ~util.foo() will have the ~util object as 'this' pointer
			wrapped = function() {
				return res.apply((!this || this === global) ? callView : this, arguments);
			};
			$extend(wrapped, res); // Attach same expandos (if any) to the wrapped function
		}
		return wrapped || res;
	}
}

/* Internal private: view._getTmpl() */
function getTemplate(tmpl) {
	return tmpl && (tmpl.fn
		? tmpl
		: this.getRsc("templates", tmpl) || $templates(tmpl)); // not yet compiled
}

//==============
// views._cnvt
//==============

function convertVal(converter, view, tagCtx, onError) {
	// Called from compiled template code for {{:}}
	// self is template object or linkCtx object
	var tag, linkCtx, value, argsLen, bindTo,
		// If tagCtx is an integer, then it is the key for the compiled function to return the boundTag tagCtx
		boundTag = typeof tagCtx === "number" && view.tmpl.bnds[tagCtx-1];

	if (onError === undefined && boundTag && boundTag._lr) { // lateRender
		onError = "";
	}
	if (onError !== undefined) {
		tagCtx = onError = {props: {}, args: [onError]};
	} else if (boundTag) {
		tagCtx = boundTag(view.data, view, $sub);
	}
	boundTag = boundTag._bd && boundTag;
	if (converter || boundTag) {
		linkCtx = view._lc; // For data-link="{cvt:...}"... See onDataLinkedTagChange
		tag = linkCtx && linkCtx.tag;
		tagCtx.view = view;
		if (!tag) {
			tag = $extend(new $sub._tg(), {
				_: {
					bnd: boundTag,
					unlinked: true,
					lt: tagCtx.lt // If a late path @some.path has not returned @some object, mark tag as late
				},
				inline: !linkCtx,
				tagName: ":",
				convert: converter,
				onArrayChange: true,
				flow: true,
				tagCtx: tagCtx,
				tagCtxs: [tagCtx],
				_is: "tag"
			});
			argsLen = tagCtx.args.length;
			if (argsLen>1) {
				bindTo = tag.bindTo = [];
				while (argsLen--) {
					bindTo.unshift(argsLen); // Bind to all the arguments - generate bindTo array: [0,1,2...]
				}
			}
			if (linkCtx) {
				linkCtx.tag = tag;
				tag.linkCtx = linkCtx;
			}
			tagCtx.ctx = extendCtx(tagCtx.ctx, (linkCtx ? linkCtx.view : view).ctx);
			tagHandlersFromProps(tag, tagCtx);
		}
		tag._er = onError && value;
		tag.ctx = tagCtx.ctx || tag.ctx || {};
		tagCtx.ctx = undefined;
		value = tag.cvtArgs()[0]; // If there is a convertBack but no convert, converter will be "true"
		tag._er = onError && value;
	} else {
		value = tagCtx.args[0];
	}

	// Call onRender (used by JsViews if present, to add binding annotations around rendered content)
	value = boundTag && view._.onRender
		? view._.onRender(value, view, tag)
		: value;
	return value != undefined ? value : "";
}

function convertArgs(tagElse, bound) { // tag.cvtArgs() or tag.cvtArgs(tagElse?, true?)
	var l, key, boundArgs, args, bindFrom, tag, converter,
		tagCtx = this;

	if (tagCtx.tagName) {
		tag = tagCtx;
		tagCtx = (tag.tagCtxs || [tagCtx])[tagElse||0];
		if (!tagCtx) {
			return;
		}
	} else {
		tag = tagCtx.tag;
	}

	bindFrom = tag.bindFrom;
	args = tagCtx.args;

	if ((converter = tag.convert) && typeof converter === STRING) {
		converter = converter === "true"
			? undefined
			: (tagCtx.view.getRsc("converters", converter) || error("Unknown converter: '" + converter + "'"));
	}

	if (converter && !bound) { // If there is a converter, use a copy of the tagCtx.args array for rendering, and replace the args[0] in
		args = args.slice(); // the copied array with the converted value. But we do not modify the value of tag.tagCtx.args[0] (the original args array)
	}
	if (bindFrom) { // Get the values of the boundArgs
		boundArgs = [];
		l = bindFrom.length;
		while (l--) {
			key = bindFrom[l];
			boundArgs.unshift(argOrProp(tagCtx, key));
		}
		if (bound) {
			args = boundArgs; // Call to bndArgs() - returns the boundArgs
		}
	}
	if (converter) {
		converter = converter.apply(tag, boundArgs || args);
		if (converter === undefined) {
			return args; // Returning undefined from a converter is equivalent to not having a converter.
		}
		bindFrom = bindFrom || [0];
		l = bindFrom.length;
		if (!$isArray(converter) || (converter.arg0 !== false && (l === 1 || converter.length !== l || converter.arg0))) {
			converter = [converter]; // Returning converter as first arg, even if converter value is an array
			bindFrom = [0];
			l = 1;
		}
		if (bound) {        // Call to bndArgs() - so apply converter to all boundArgs
			args = converter; // The array of values returned from the converter
		} else {            // Call to cvtArgs()
			while (l--) {
				key = bindFrom[l];
				if (+key === key) {
					args[key] = converter[l];
				}
			}
		}
	}
	return args;
}

function argOrProp(context, key) {
	context = context[+key === key ? "args" : "props"];
	return context && context[key];
}

function convertBoundArgs(tagElse) { // tag.bndArgs()
	return this.cvtArgs(tagElse, 1);
}

//=============
// views.tag
//=============

/* view.getRsc() */
function getResource(resourceType, itemName) {
	var res, store,
		view = this;
	if (typeof itemName === STRING) {
		while ((res === undefined) && view) {
			store = view.tmpl && view.tmpl[resourceType];
			res = store && store[itemName];
			view = view.parent;
		}
		return res || $views[resourceType][itemName];
	}
}

function renderTag(tagName, parentView, tmpl, tagCtxs, isUpdate, onError) {
	function bindToOrBindFrom(type) {
		var bindArray = tag[type];

		if (bindArray !== undefined) {
			bindArray = $isArray(bindArray) ? bindArray : [bindArray];
			m = bindArray.length;
			while (m--) {
				key = bindArray[m];
				if (!isNaN(parseInt(key))) {
					bindArray[m] = parseInt(key); // Convert "0" to 0, etc.
				}
			}
		}

		return bindArray || [0];
	}

	parentView = parentView || topView;
	var tag, tagDef, template, tags, attr, parentTag, l, m, n, itemRet, tagCtx, tagCtxCtx, ctxPrm, bindTo, bindFrom, initVal,
		content, callInit, mapDef, thisMap, args, bdArgs, props, tagDataMap, contentCtx, key, bindFromLength, bindToLength, linkedElement, defaultCtx,
		i = 0,
		ret = "",
		linkCtx = parentView._lc || false, // For data-link="{myTag...}"... See onDataLinkedTagChange
		ctx = parentView.ctx,
		parentTmpl = tmpl || parentView.tmpl,
		// If tagCtxs is an integer, then it is the key for the compiled function to return the boundTag tagCtxs
		boundTag = typeof tagCtxs === "number" && parentView.tmpl.bnds[tagCtxs-1];

	if (tagName._is === "tag") {
		tag = tagName;
		tagName = tag.tagName;
		tagCtxs = tag.tagCtxs;
		template = tag.template;
	} else {
		tagDef = parentView.getRsc("tags", tagName) || error("Unknown tag: {{" + tagName + "}} ");
		template = tagDef.template;
	}
	if (onError === undefined && boundTag && (boundTag._lr = (tagDef.lateRender && boundTag._lr!== false || boundTag._lr))) {
		onError = ""; // If lateRender, set temporary onError, to skip initial rendering (and render just "")
	}
	if (onError !== undefined) {
		ret += onError;
		tagCtxs = onError = [{props: {}, args: [], params: {props:{}}}];
	} else if (boundTag) {
		tagCtxs = boundTag(parentView.data, parentView, $sub);
	}

	l = tagCtxs.length;
	for (; i < l; i++) {
		tagCtx = tagCtxs[i];
		content = tagCtx.tmpl;
		if (!linkCtx || !linkCtx.tag || i && !linkCtx.tag.inline || tag._er || content && +content===content) {
			// Initialize tagCtx
			// For block tags, tagCtx.tmpl is an integer > 0
			if (content && parentTmpl.tmpls) {
				tagCtx.tmpl = tagCtx.content = parentTmpl.tmpls[content - 1]; // Set the tmpl property to the content of the block tag
			}
			tagCtx.index = i;
			tagCtx.ctxPrm = contextParameter;
			tagCtx.render = renderContent;
			tagCtx.cvtArgs = convertArgs;
			tagCtx.bndArgs = convertBoundArgs;
			tagCtx.view = parentView;
			tagCtx.ctx = extendCtx(extendCtx(tagCtx.ctx, tagDef && tagDef.ctx), ctx); // Clone and extend parentView.ctx
		}
		if (tmpl = tagCtx.props.tmpl) {
			// If the tmpl property is overridden, set the value (when initializing, or, in case of binding: ^tmpl=..., when updating)
			tagCtx.tmpl = parentView._getTmpl(tmpl);
			tagCtx.content = tagCtx.content || tagCtx.tmpl;
		}

		if (!tag) {
			// This will only be hit for initial tagCtx (not for {{else}}) - if the tag instance does not exist yet
			// If the tag has not already been instantiated, we will create a new instance.
			// ~tag will access the tag, even within the rendering of the template content of this tag.
			// From child/descendant tags, can access using ~tag.parent, or ~parentTags.tagName
			tag = new tagDef._ctr();
			callInit = !!tag.init;

			tag.parent = parentTag = ctx && ctx.tag;
			tag.tagCtxs = tagCtxs;

			if (linkCtx) {
				tag.inline = false;
				linkCtx.tag = tag;
			}
			tag.linkCtx = linkCtx;
			if (tag._.bnd = boundTag || linkCtx.fn) {
				// Bound if {^{tag...}} or data-link="{tag...}"
				tag._.ths = tagCtx.params.props["this"]; // Tag has a this=expr binding, to get javascript reference to tag instance
				tag._.lt = tagCtxs.lt; // If a late path @some.path has not returned @some object, mark tag as late
				tag._.arrVws = {};
			} else if (tag.dataBoundOnly) {
				error(tagName + " must be data-bound:\n{^{" + tagName + "}}");
			}
			//TODO better perf for childTags() - keep child tag.tags array, (and remove child, when disposed)
			// tag.tags = [];
		} else if (linkCtx && linkCtx.fn._lr) {
			callInit = !!tag.init;
		}
		tagDataMap = tag.dataMap;

		tagCtx.tag = tag;
		if (tagDataMap && tagCtxs) {
			tagCtx.map = tagCtxs[i].map; // Copy over the compiled map instance from the previous tagCtxs to the refreshed ones
		}
		if (!tag.flow) {
			tagCtxCtx = tagCtx.ctx = tagCtx.ctx || {};

			// tags hash: tag.ctx.tags, merged with parentView.ctx.tags,
			tags = tag.parents = tagCtxCtx.parentTags = ctx && extendCtx(tagCtxCtx.parentTags, ctx.parentTags) || {};
			if (parentTag) {
				tags[parentTag.tagName] = parentTag;
				//TODO better perf for childTags: parentTag.tags.push(tag);
			}
			tags[tag.tagName] = tagCtxCtx.tag = tag;
			tagCtxCtx.tagCtx = tagCtx;
		}
	}
	if (!(tag._er = onError)) {
		tagHandlersFromProps(tag, tagCtxs[0]);
		tag.rendering = {rndr: tag.rendering}; // Provide object for state during render calls to tag and elses. (Used by {{if}} and {{for}}...)
		for (i = 0; i < l; i++) { // Iterate tagCtx for each {{else}} block
			tagCtx = tag.tagCtx = tagCtxs[i];
			props = tagCtx.props;
			tag.ctx = tagCtx.ctx;

			if (!i) {
				if (callInit) {
					tag.init(tagCtx, linkCtx, tag.ctx);
					callInit = undefined;
				}
				if (!tagCtx.args.length && tagCtx.argDefault !== false && tag.argDefault !== false) {
					tagCtx.args = args = [tagCtx.view.data]; // Missing first arg defaults to the current data context
					tagCtx.params.args = ["#data"];
				}

				bindTo = bindToOrBindFrom("bindTo");

				if (tag.bindTo !== undefined) {
					tag.bindTo = bindTo;
				}

				if (tag.bindFrom !== undefined) {
					tag.bindFrom = bindToOrBindFrom("bindFrom");
				} else if (tag.bindTo) {
					tag.bindFrom = tag.bindTo = bindTo;
				}
				bindFrom = tag.bindFrom || bindTo;

				bindToLength = bindTo.length;
				bindFromLength = bindFrom.length;

				if (tag._.bnd && (linkedElement = tag.linkedElement)) {
					tag.linkedElement = linkedElement = $isArray(linkedElement) ? linkedElement: [linkedElement];

					if (bindToLength !== linkedElement.length) {
						error("linkedElement not same length as bindTo");
					}
				}
				if (linkedElement = tag.linkedCtxParam) {
					tag.linkedCtxParam = linkedElement = $isArray(linkedElement) ? linkedElement: [linkedElement];

					if (bindFromLength !== linkedElement.length) {
						error("linkedCtxParam not same length as bindFrom/bindTo");
					}
				}

				if (bindFrom) {
					tag._.fromIndex = {}; // Hash of bindFrom index which has same path value as bindTo index. fromIndex = tag._.fromIndex[toIndex]
					tag._.toIndex = {}; // Hash of bindFrom index which has same path value as bindTo index. fromIndex = tag._.fromIndex[toIndex]
					n = bindFromLength;
					while (n--) {
						key = bindFrom[n];
						m = bindToLength;
						while (m--) {
							if (key === bindTo[m]) {
								tag._.fromIndex[m] = n;
								tag._.toIndex[n] = m;
							}
						}
					}
				}

				if (linkCtx) {
					// Set attr on linkCtx to ensure outputting to the correct target attribute.
					// Setting either linkCtx.attr or this.attr in the init() allows per-instance choice of target attrib.
					linkCtx.attr = tag.attr = linkCtx.attr || tag.attr || linkCtx._dfAt;
				}
				attr = tag.attr;
				tag._.noVws = attr && attr !== HTML;
			}
			args = tag.cvtArgs(i);
			if (tag.linkedCtxParam) {
				bdArgs = tag.cvtArgs(i, 1);
				m = bindFromLength;
				defaultCtx = tag.constructor.prototype.ctx;
				while (m--) {
					if (ctxPrm = tag.linkedCtxParam[m]) {
						key = bindFrom[m];
						initVal = bdArgs[m];
						// Create tag contextual parameter
						tagCtx.ctx[ctxPrm] = $sub._cp(
							defaultCtx && initVal === undefined ? defaultCtx[ctxPrm]: initVal,
							initVal !== undefined && argOrProp(tagCtx.params, key),
							tagCtx.view,
							tag._.bnd && {tag: tag, cvt: tag.convert, ind: m, tagElse: i}
						);
					}
				}
			}
			if ((mapDef = props.dataMap || tagDataMap) && (args.length || props.dataMap)) {
				thisMap = tagCtx.map;
				if (!thisMap || thisMap.src !== args[0] || isUpdate) {
					if (thisMap && thisMap.src) {
						thisMap.unmap(); // only called if observable map - not when only used in JsRender, e.g. by {{props}}
					}
					mapDef.map(args[0], tagCtx, thisMap, !tag._.bnd);
					thisMap = tagCtx.map;
				}
				args = [thisMap.tgt];
			}

			itemRet = undefined;
			if (tag.render) {
				itemRet = tag.render.apply(tag, args);
				if (parentView.linked && itemRet && !rWrappedInViewMarker.test(itemRet)) {
					// When a tag renders content from the render method, with data linking then we need to wrap with view markers, if absent,
					// to provide a contentView for the tag, which will correctly dispose bindings if deleted. The 'tmpl' for this view will
					// be a dumbed-down template which will always return the itemRet string (no matter what the data is). The itemRet string
					// is not compiled as template markup, so can include "{{" or "}}" without triggering syntax errors
					tmpl = { // 'Dumbed-down' template which always renders 'static' itemRet string
						links: []
					};
					tmpl.render = tmpl.fn = function() {
						return itemRet;
					};
					itemRet = renderWithViews(tmpl, parentView.data, undefined, true, parentView, undefined, undefined, tag);
				}
			}
			if (!args.length) {
				args = [parentView]; // no arguments - (e.g. {{else}}) get data context from view.
			}
			if (itemRet === undefined) {
				contentCtx = args[0]; // Default data context for wrapped block content is the first argument
				if (tag.contentCtx) { // Set tag.contentCtx to true, to inherit parent context, or to a function to provide alternate context.
					contentCtx = tag.contentCtx === true ? parentView : tag.contentCtx(contentCtx);
				}
				itemRet = tagCtx.render(contentCtx, true) || (isUpdate ? undefined : "");
			}
			ret = ret
				? ret + (itemRet || "")
				: itemRet !== undefined
					? "" + itemRet
					: undefined; // If no return value from render, and no template/content tagCtx.render(...), return undefined
		}
		tag.rendering = tag.rendering.rndr; // Remove tag.rendering object (if this is outermost render call. (In case of nested calls)
	}
	tag.tagCtx = tagCtxs[0];
	tag.ctx = tag.tagCtx.ctx;

	if (tag._.noVws && tag.inline) {
		// inline tag with attr set to "text" will insert HTML-encoded content - as if it was element-based innerText
		ret = attr === "text"
			? $converters.html(ret)
			: "";
	}
	return boundTag && parentView._.onRender
		// Call onRender (used by JsViews if present, to add binding annotations around rendered content)
		? parentView._.onRender(ret, parentView, tag)
		: ret;
}

//=================
// View constructor
//=================

function View(context, type, parentView, data, template, key, onRender, contentTmpl) {
	// Constructor for view object in view hierarchy. (Augmented by JsViews if JsViews is loaded)
	var views, parentView_, tag, self_,
		self = this,
		isArray = type === "array";
		// If the data is an array, this is an 'array view' with a views array for each child 'item view'
		// If the data is not an array, this is an 'item view' with a views 'hash' object for any child nested views

	self.content = contentTmpl;
	self.views = isArray ? [] : {};
	self.data = data;
	self.tmpl = template;
	self_ = self._ = {
		key: 0,
		// ._.useKey is non zero if is not an 'array view' (owning a data array). Use this as next key for adding to child views hash
		useKey: isArray ? 0 : 1,
		id: "" + viewId++,
		onRender: onRender,
		bnds: {}
	};
	self.linked = !!onRender;
	self.type = type || "top";
	if (type) {
		self.cache = {_ct: $subSettings._cchCt}; // Used for caching results of computed properties and helpers (view.getCache)
	}

	if (!parentView || parentView.type === "top") {
		(self.ctx = context || {}).root = self.data;
	}

	if (self.parent = parentView) {
		self.root = parentView.root || self; // view whose parent is top view
		views = parentView.views;
		parentView_ = parentView._;
		self.isTop = parentView_.scp; // Is top content view of a link("#container", ...) call
		self.scope = (!context.tag || context.tag === parentView.ctx.tag) && !self.isTop && parentView.scope || self;
		// Scope for contextParams - closest non flow tag ancestor or root view
		if (parentView_.useKey) {
			// Parent is not an 'array view'. Add this view to its views object
			// self._key = is the key in the parent view hash
			views[self_.key = "_" + parentView_.useKey++] = self;
			self.index = indexStr;
			self.getIndex = getNestedIndex;
		} else if (views.length === (self_.key = self.index = key)) { // Parent is an 'array view'. Add this view to its views array
			views.push(self); // Adding to end of views array. (Using push when possible - better perf than splice)
		} else {
			views.splice(key, 0, self); // Inserting in views array
		}
		// If no context was passed in, use parent context
		// If context was passed in, it should have been merged already with parent context
		self.ctx = context || parentView.ctx;
	} else if (type) {
		self.root = self; // view whose parent is top view
	}
}

View.prototype = {
	get: getView,
	getIndex: getIndex,
	ctxPrm: contextParameter,
	getRsc: getResource,
	_getTmpl: getTemplate,
	_getOb: getPathObject,
	getCache: function(key) { // Get cached value of computed value
		if ($subSettings._cchCt > this.cache._ct) {
			this.cache = {_ct: $subSettings._cchCt};
		}
		return this.cache[key] !== undefined ? this.cache[key] : (this.cache[key] = cpFnStore[key](this.data, this, $sub));
	},
	_is: "view"
};

//====================================================
// Registration
//====================================================

function compileChildResources(parentTmpl) {
	var storeName, storeNames, resources;
	for (storeName in jsvStores) {
		storeNames = storeName + "s";
		if (parentTmpl[storeNames]) {
			resources = parentTmpl[storeNames];        // Resources not yet compiled
			parentTmpl[storeNames] = {};               // Remove uncompiled resources
			$views[storeNames](resources, parentTmpl); // Add back in the compiled resources
		}
	}
}

//===============
// compileTag
//===============

function compileTag(name, tagDef, parentTmpl) {
	var tmpl, baseTag, prop,
		compiledDef = new $sub._tg();

	function Tag() {
		var tag = this;
		tag._ = {
			unlinked: true
		};
		tag.inline = true;
		tag.tagName = name;
	}

	if ($isFunction(tagDef)) {
		// Simple tag declared as function. No presenter instantation.
		tagDef = {
			depends: tagDef.depends,
			render: tagDef
		};
	} else if (typeof tagDef === STRING) {
		tagDef = {template: tagDef};
	}

	if (baseTag = tagDef.baseTag) {
		tagDef.flow = !!tagDef.flow; // Set flow property, so defaults to false even if baseTag has flow=true
		baseTag = typeof baseTag === STRING
			? (parentTmpl && parentTmpl.tags[baseTag] || $tags[baseTag])
			: baseTag;
		if (!baseTag) {
			error('baseTag: "' + tagDef.baseTag + '" not found');
		}
		compiledDef = $extend(compiledDef, baseTag);

		for (prop in tagDef) {
			compiledDef[prop] = getMethod(baseTag[prop], tagDef[prop]);
		}
	} else {
		compiledDef = $extend(compiledDef, tagDef);
	}

	// Tag declared as object, used as the prototype for tag instantiation (control/presenter)
	if ((tmpl = compiledDef.template) !== undefined) {
		compiledDef.template = typeof tmpl === STRING ? ($templates[tmpl] || $templates(tmpl)) : tmpl;
	}
	(Tag.prototype = compiledDef).constructor = compiledDef._ctr = Tag;

	if (parentTmpl) {
		compiledDef._parentTmpl = parentTmpl;
	}
	return compiledDef;
}

function baseApply(args) {
	// In derived method (or handler declared declaratively as in {{:foo onChange=~fooChanged}} can call base method,
	// using this.baseApply(arguments) (Equivalent to this._superApply(arguments) in jQuery UI)
	return this.base.apply(this, args);
}

//===============
// compileTmpl
//===============

function compileTmpl(name, tmpl, parentTmpl, options) {
	// tmpl is either a template object, a selector for a template script block, or the name of a compiled template

	//==== nested functions ====
	function lookupTemplate(value) {
		// If value is of type string - treat as selector, or name of compiled template
		// Return the template object, if already compiled, or the markup string
		var currentName, tmpl;
		if ((typeof value === STRING) || value.nodeType > 0 && (elem = value)) {
			if (!elem) {
				if (/^\.?\/[^\\:*?"<>]*$/.test(value)) {
					// value="./some/file.html" (or "/some/file.html")
					// If the template is not named, use "./some/file.html" as name.
					if (tmpl = $templates[name = name || value]) {
						value = tmpl;
					} else {
						// BROWSER-SPECIFIC CODE (not on Node.js):
						// Look for server-generated script block with id "./some/file.html"
						elem = document.getElementById(value);
					}
				} else if (value.charAt(0) === "#") {
					elem = document.getElementById(value.slice(1));
				} if (!elem && $.fn && !$sub.rTmpl.test(value)) {
					try {
						elem = $(value, document)[0]; // if jQuery is loaded, test for selector returning elements, and get first element
					} catch (e) {}
				}// END BROWSER-SPECIFIC CODE
			} //BROWSER-SPECIFIC CODE
			if (elem) {
				if (elem.tagName !== "SCRIPT") {
					error(value + ": Use script block, not " + elem.tagName);
				}
				if (options) {
					// We will compile a new template using the markup in the script element
					value = elem.innerHTML;
				} else {
					// We will cache a single copy of the compiled template, and associate it with the name
					// (renaming from a previous name if there was one).
					currentName = elem.getAttribute(tmplAttr);
					if (currentName) {
						if (currentName !== jsvTmpl) {
							value = $templates[currentName];
							delete $templates[currentName];
						} else if ($.fn) {
							value = $.data(elem)[jsvTmpl]; // Get cached compiled template
						}
					}
					if (!currentName || !value) { // Not yet compiled, or cached version lost
						name = name || ($.fn ? jsvTmpl : value);
						value = compileTmpl(name, elem.innerHTML, parentTmpl, options);
					}
					value.tmplName = name = name || currentName;
					if (name !== jsvTmpl) {
						$templates[name] = value;
					}
					elem.setAttribute(tmplAttr, name);
					if ($.fn) {
						$.data(elem, jsvTmpl, value);
					}
				}
			} // END BROWSER-SPECIFIC CODE
			elem = undefined;
		} else if (!value.fn) {
			value = undefined;
			// If value is not a string. HTML element, or compiled template, return undefined
		}
		return value;
	}

	var elem, compiledTmpl,
		tmplOrMarkup = tmpl = tmpl || "";
	$sub._html = $converters.html;

	//==== Compile the template ====
	if (options === 0) {
		options = undefined;
		tmplOrMarkup = lookupTemplate(tmplOrMarkup); // Top-level compile so do a template lookup
	}

	// If options, then this was already compiled from a (script) element template declaration.
	// If not, then if tmpl is a template object, use it for options
	options = options || (tmpl.markup
		? tmpl.bnds
			? $extend({}, tmpl)
			: tmpl
		: {}
	);

	options.tmplName = options.tmplName || name || "unnamed";
	if (parentTmpl) {
		options._parentTmpl = parentTmpl;
	}
	// If tmpl is not a markup string or a selector string, then it must be a template object
	// In that case, get it from the markup property of the object
	if (!tmplOrMarkup && tmpl.markup && (tmplOrMarkup = lookupTemplate(tmpl.markup)) && tmplOrMarkup.fn) {
		// If the string references a compiled template object, need to recompile to merge any modified options
		tmplOrMarkup = tmplOrMarkup.markup;
	}
	if (tmplOrMarkup !== undefined) {
		if (tmplOrMarkup.render || tmpl.render) {
			// tmpl is already compiled, so use it
			if (tmplOrMarkup.tmpls) {
				compiledTmpl = tmplOrMarkup;
			}
		} else {
			// tmplOrMarkup is a markup string, not a compiled template
			// Create template object
			tmpl = tmplObject(tmplOrMarkup, options);
			// Compile to AST and then to compiled function
			tmplFn(tmplOrMarkup.replace(rEscapeQuotes, "\\$&"), tmpl);
		}
		if (!compiledTmpl) {
			compiledTmpl = $extend(function() {
				return compiledTmpl.render.apply(compiledTmpl, arguments);
			}, tmpl);

			compileChildResources(compiledTmpl);
		}
		return compiledTmpl;
	}
}

//==== /end of function compileTmpl ====

//=================
// compileViewModel
//=================

function getDefaultVal(defaultVal, data) {
	return $isFunction(defaultVal)
		? defaultVal.call(data)
		: defaultVal;
}

function addParentRef(ob, ref, parent) {
	Object.defineProperty(ob, ref, {
		value: parent,
		configurable: true
	});
}

function compileViewModel(name, type) {
	var i, constructor, parent,
		viewModels = this,
		getters = type.getters,
		extend = type.extend,
		id = type.id,
		proto = $.extend({
			_is: name || "unnamed",
			unmap: unmap,
			merge: merge
		}, extend),
		args = "",
		cnstr = "",
		getterCount = getters ? getters.length : 0,
		$observable = $.observable,
		getterNames = {};

	function JsvVm(args) {
		constructor.apply(this, args);
	}

	function vm() {
		return new JsvVm(arguments);
	}

	function iterate(data, action) {
		var getterType, defaultVal, prop, ob, parentRef,
			j = 0;
		for (; j < getterCount; j++) {
			prop = getters[j];
			getterType = undefined;
			if (typeof prop !== STRING) {
				getterType = prop;
				prop = getterType.getter;
				parentRef = getterType.parentRef;
			}
			if ((ob = data[prop]) === undefined && getterType && (defaultVal = getterType.defaultVal) !== undefined) {
				ob = getDefaultVal(defaultVal, data);
			}
			action(ob, getterType && viewModels[getterType.type], prop, parentRef);
		}
	}

	function map(data) {
		data = typeof data === STRING
			? JSON.parse(data) // Accept JSON string
			: data;            // or object/array
		var l, prop, childOb, parentRef,
			j = 0,
			ob = data,
			arr = [];

		if ($isArray(data)) {
			data = data || [];
			l = data.length;
			for (; j<l; j++) {
				arr.push(this.map(data[j]));
			}
			arr._is = name;
			arr.unmap = unmap;
			arr.merge = merge;
			return arr;
		}

		if (data) {
			iterate(data, function(ob, viewModel) {
				if (viewModel) { // Iterate to build getters arg array (value, or mapped value)
					ob = viewModel.map(ob);
				}
				arr.push(ob);
			});
			ob = this.apply(this, arr); // Instantiate this View Model, passing getters args array to constructor
			j = getterCount;
			while (j--) {
				childOb = arr[j];
				parentRef = getters[j].parentRef;
				if (parentRef && childOb && childOb.unmap) {
					if ($isArray(childOb)) {
						l = childOb.length;
						while (l--) {
							addParentRef(childOb[l], parentRef, ob);
						}
					} else {
						addParentRef(childOb, parentRef, ob);
					}
				}
			}
			for (prop in data) { // Copy over any other properties. that are not get/set properties
				if (prop !== $expando && !getterNames[prop]) {
					ob[prop] = data[prop];
				}
			}
		}
		return ob;
	}

	function merge(data, parent, parentRef) {
		data = typeof data === STRING
			? JSON.parse(data) // Accept JSON string
			: data;            // or object/array

		var j, l, m, prop, mod, found, assigned, ob, newModArr, childOb,
			k = 0,
			model = this;

		if ($isArray(model)) {
			assigned = {};
			newModArr = [];
			l = data.length;
			m = model.length;
			for (; k<l; k++) {
				ob = data[k];
				found = false;
				for (j=0; j<m && !found; j++) {
					if (assigned[j]) {
						continue;
					}
					mod = model[j];

					if (id) {
						assigned[j] = found = typeof id === STRING
						? (ob[id] && (getterNames[id] ? mod[id]() : mod[id]) === ob[id])
						: id(mod, ob);
					}
				}
				if (found) {
					mod.merge(ob);
					newModArr.push(mod);
				} else {
					newModArr.push(childOb = vm.map(ob));
					if (parentRef) {
						addParentRef(childOb, parentRef, parent);
					}
				}
			}
			if ($observable) {
				$observable(model).refresh(newModArr, true);
			} else {
				model.splice.apply(model, [0, model.length].concat(newModArr));
			}
			return;
		}
		iterate(data, function(ob, viewModel, getter, parentRef) {
			if (viewModel) {
				model[getter]().merge(ob, model, parentRef); // Update typed property
			} else if (model[getter]() !== ob) {
				model[getter](ob); // Update non-typed property
			}
		});
		for (prop in data) {
			if (prop !== $expando && !getterNames[prop]) {
				model[prop] = data[prop];
			}
		}
	}

	function unmap() {
		var ob, prop, getterType, arr, value,
			k = 0,
			model = this;

		function unmapArray(modelArr) {
			var arr = [],
				i = 0,
				l = modelArr.length;
			for (; i<l; i++) {
				arr.push(modelArr[i].unmap());
			}
			return arr;
		}

		if ($isArray(model)) {
			return unmapArray(model);
		}
		ob = {};
		for (; k < getterCount; k++) {
			prop = getters[k];
			getterType = undefined;
			if (typeof prop !== STRING) {
				getterType = prop;
				prop = getterType.getter;
			}
			value = model[prop]();
			ob[prop] = getterType && value && viewModels[getterType.type]
				? $isArray(value)
					? unmapArray(value)
					: value.unmap()
				: value;
		}
		for (prop in model) {
			if (model.hasOwnProperty(prop) && (prop.charAt(0) !== "_" || !getterNames[prop.slice(1)]) && prop !== $expando && !$isFunction(model[prop])) {
				ob[prop] = model[prop];
			}
		}
		return ob;
	}

	JsvVm.prototype = proto;

	for (i=0; i < getterCount; i++) {
		(function(getter) {
			getter = getter.getter || getter;
			getterNames[getter] = i+1;
			var privField = "_" + getter;

			args += (args ? "," : "") + getter;
			cnstr += "this." + privField + " = " + getter + ";\n";
			proto[getter] = proto[getter] || function(val) {
				if (!arguments.length) {
					return this[privField]; // If there is no argument, use as a getter
				}
				if ($observable) {
					$observable(this).setProperty(getter, val);
				} else {
					this[privField] = val;
				}
			};

			if ($observable) {
				proto[getter].set = proto[getter].set || function(val) {
					this[privField] = val; // Setter called by observable property change
				};
			}
		})(getters[i]);
	}

	// Constructor for new viewModel instance.
	cnstr = new Function(args, cnstr);

	constructor = function() {
		cnstr.apply(this, arguments);
		// Pass additional parentRef str and parent obj to have a parentRef pointer on instance
		if (parent = arguments[getterCount + 1]) {
			addParentRef(this, arguments[getterCount], parent);
		}
	};

	constructor.prototype = proto;
	proto.constructor = constructor;

	vm.map = map;
	vm.getters = getters;
	vm.extend = extend;
	vm.id = id;
	return vm;
}

function tmplObject(markup, options) {
	// Template object constructor
	var htmlTag,
		wrapMap = $subSettingsAdvanced._wm || {}, // Only used in JsViews. Otherwise empty: {}
		tmpl = {
			tmpls: [],
			links: {}, // Compiled functions for link expressions
			bnds: [],
			_is: "template",
			render: renderContent
		};

	if (options) {
		tmpl = $extend(tmpl, options);
	}

	tmpl.markup = markup;
	if (!tmpl.htmlTag) {
		// Set tmpl.tag to the top-level HTML tag used in the template, if any...
		htmlTag = rFirstElem.exec(markup);
		tmpl.htmlTag = htmlTag ? htmlTag[1].toLowerCase() : "";
	}
	htmlTag = wrapMap[tmpl.htmlTag];
	if (htmlTag && htmlTag !== wrapMap.div) {
		// When using JsViews, we trim templates which are inserted into HTML contexts where text nodes are not rendered (i.e. not 'Phrasing Content').
		// Currently not trimmed for <li> tag. (Not worth adding perf cost)
		tmpl.markup = $.trim(tmpl.markup);
	}

	return tmpl;
}

//==============
// registerStore
//==============

/**
* Internal. Register a store type (used for template, tags, helpers, converters)
*/
function registerStore(storeName, storeSettings) {

/**
* Generic store() function to register item, named item, or hash of items
* Also used as hash to store the registered items
* Used as implementation of $.templates(), $.views.templates(), $.views.tags(), $.views.helpers() and $.views.converters()
*
* @param {string|hash} name         name - or selector, in case of $.templates(). Or hash of items
* @param {any}         [item]       (e.g. markup for named template)
* @param {template}    [parentTmpl] For item being registered as private resource of template
* @returns {any|$.views} item, e.g. compiled template - or $.views in case of registering hash of items
*/
	function theStore(name, item, parentTmpl) {
		// The store is also the function used to add items to the store. e.g. $.templates, or $.views.tags

		// For store of name 'thing', Call as:
		//    $.views.things(items[, parentTmpl]),
		// or $.views.things(name[, item, parentTmpl])

		var compile, itemName, thisStore, cnt,
			onStore = $sub.onStore[storeName];

		if (name && typeof name === OBJECT && !name.nodeType && !name.markup && !name.getTgt && !(storeName === "viewModel" && name.getters || name.extend)) {
			// Call to $.views.things(items[, parentTmpl]),

			// Adding items to the store
			// If name is a hash, then item is parentTmpl. Iterate over hash and call store for key.
			for (itemName in name) {
				theStore(itemName, name[itemName], item);
			}
			return item || $views;
		}
		// Adding a single unnamed item to the store
		if (name &&  typeof name !== STRING) { // name must be a string
			parentTmpl = item;
			item = name;
			name = undefined;
		}
		thisStore = parentTmpl
			? storeName === "viewModel"
				? parentTmpl
				: (parentTmpl[storeNames] = parentTmpl[storeNames] || {})
			: theStore;
		compile = storeSettings.compile;

		if (item === undefined) {
			item = compile ? name : thisStore[name];
			name = undefined;
		}
		if (item === null) {
			// If item is null, delete this entry
			if (name) {
				delete thisStore[name];
			}
		} else {
			if (compile) {
				item = compile.call(thisStore, name, item, parentTmpl, 0) || {};
				item._is = storeName; // Only do this for compiled objects (tags, templates...)
			}
			if (name) {
				thisStore[name] = item;
			}
		}
		if (onStore) {
			// e.g. JsViews integration
			onStore(name, item, parentTmpl, compile);
		}
		return item;
	}

	var storeNames = storeName + "s";
	$views[storeNames] = theStore;
}

/**
* Add settings such as:
* $.views.settings.allowCode(true)
* @param {boolean} value
* @returns {Settings}
*
* allowCode = $.views.settings.allowCode()
* @returns {boolean}
*/
function addSetting(st) {
	$viewsSettings[st] = $viewsSettings[st] || function(value) {
		return arguments.length
			? ($subSettings[st] = value, $viewsSettings)
			: $subSettings[st];
	};
}

//========================
// dataMap for render only
//========================

function dataMap(mapDef) {
	function Map(source, options) {
		this.tgt = mapDef.getTgt(source, options);
		options.map = this;
	}

	if ($isFunction(mapDef)) {
		// Simple map declared as function
		mapDef = {
			getTgt: mapDef
		};
	}

	if (mapDef.baseMap) {
		mapDef = $extend($extend({}, mapDef.baseMap), mapDef);
	}

	mapDef.map = function(source, options) {
		return new Map(source, options);
	};
	return mapDef;
}

//==============
// renderContent
//==============

/** Render the template as a string, using the specified data and helpers/context
* $("#tmpl").render(), tmpl.render(), tagCtx.render(), $.render.namedTmpl()
*
* @param {any}        data
* @param {hash}       [context]           helpers or context
* @param {boolean}    [noIteration]
* @param {View}       [parentView]        internal
* @param {string}     [key]               internal
* @param {function}   [onRender]          internal
* @returns {string}   rendered template   internal
*/
function renderContent(data, context, noIteration, parentView, key, onRender) {
	var i, l, tag, tmpl, tagCtx, isTopRenderCall, prevData, prevIndex,
		view = parentView,
		result = "";

	if (context === true) {
		noIteration = context; // passing boolean as second param - noIteration
		context = undefined;
	} else if (typeof context !== OBJECT) {
		context = undefined; // context must be a boolean (noIteration) or a plain object
	}

	if (tag = this.tag) {
		// This is a call from renderTag or tagCtx.render(...)
		tagCtx = this;
		view = view || tagCtx.view;
		tmpl = view._getTmpl(tag.template || tagCtx.tmpl);
		if (!arguments.length) {
			data = tag.contentCtx && $isFunction(tag.contentCtx)
				? data = tag.contentCtx(data)
				: view; // Default data context for wrapped block content is the first argument
		}
	} else {
		// This is a template.render(...) call
		tmpl = this;
	}

	if (tmpl) {
		if (!parentView && data && data._is === "view") {
			view = data; // When passing in a view to render or link (and not passing in a parent view) use the passed-in view as parentView
		}

		if (view && data === view) {
			// Inherit the data from the parent view.
			data = view.data;
		}

		isTopRenderCall = !view;
		isRenderCall = isRenderCall || isTopRenderCall;
		if (isTopRenderCall) {
			(context = context || {}).root = data; // Provide ~root as shortcut to top-level data.
		}
		if (!isRenderCall || $subSettingsAdvanced.useViews || tmpl.useViews || view && view !== topView) {
			result = renderWithViews(tmpl, data, context, noIteration, view, key, onRender, tag);
		} else {
			if (view) { // In a block
				prevData = view.data;
				prevIndex = view.index;
				view.index = indexStr;
			} else {
				view = topView;
				prevData = view.data;
				view.data = data;
				view.ctx = context;
			}
			if ($isArray(data) && !noIteration) {
				// Create a view for the array, whose child views correspond to each data item. (Note: if key and parentView are passed in
				// along with parent view, treat as insert -e.g. from view.addViews - so parentView is already the view item for array)
				for (i = 0, l = data.length; i < l; i++) {
					view.index = i;
					view.data = data[i];
					result += tmpl.fn(data[i], view, $sub);
				}
			} else {
				view.data = data;
				result += tmpl.fn(data, view, $sub);
			}
			view.data = prevData;
			view.index = prevIndex;
		}
		if (isTopRenderCall) {
			isRenderCall = undefined;
		}
	}
	return result;
}

function renderWithViews(tmpl, data, context, noIteration, view, key, onRender, tag) {
	// Render template against data as a tree of subviews (nested rendered template instances), or as a string (top-level template).
	// If the data is the parent view, treat as noIteration, re-render with the same data context.
	// tmpl can be a string (e.g. rendered by a tag.render() method), or a compiled template.
	var i, l, newView, childView, itemResult, swapContent, contentTmpl, outerOnRender, tmplName, itemVar, newCtx, tagCtx, noLinking,
		result = "";

	if (tag) {
		// This is a call from renderTag or tagCtx.render(...)
		tmplName = tag.tagName;
		tagCtx = tag.tagCtx;
		context = context ? extendCtx(context, tag.ctx) : tag.ctx;

		if (tmpl === view.content) { // {{xxx tmpl=#content}}
			contentTmpl = tmpl !== view.ctx._wrp // We are rendering the #content
				? view.ctx._wrp // #content was the tagCtx.props.tmpl wrapper of the block content - so within this view, #content will now be the view.ctx._wrp block content
				: undefined; // #content was the view.ctx._wrp block content - so within this view, there is no longer any #content to wrap.
		} else if (tmpl !== tagCtx.content) {
			if (tmpl === tag.template) { // Rendering {{tag}} tag.template, replacing block content.
				contentTmpl = tagCtx.tmpl; // Set #content to block content (or wrapped block content if tagCtx.props.tmpl is set)
				context._wrp = tagCtx.content; // Pass wrapped block content to nested views
			} else { // Rendering tagCtx.props.tmpl wrapper
				contentTmpl = tagCtx.content || view.content; // Set #content to wrapped block content
			}
		} else {
			contentTmpl = view.content; // Nested views inherit same wrapped #content property
		}

		if (tagCtx.props.link === false) {
			// link=false setting on block tag
			// We will override inherited value of link by the explicit setting link=false taken from props
			// The child views of an unlinked view are also unlinked. So setting child back to true will not have any effect.
			context = context || {};
			context.link = false;
		}
	}

	if (view) {
		onRender = onRender || view._.onRender;
		noLinking = context && context.link === false;

		if (noLinking && view._.nl) {
			onRender = undefined;
		}

		context = extendCtx(context, view.ctx);
		tagCtx = !tag && view.tag
			? view.tag.tagCtxs[view.tagElse]
			: tagCtx;
	}

	if (itemVar = tagCtx && tagCtx.props.itemVar) {
		if (itemVar[0] !== "~") {
			syntaxError("Use itemVar='~myItem'");
		}
		itemVar = itemVar.slice(1);
	}

	if (key === true) {
		swapContent = true;
		key = 0;
	}

	// If link===false, do not call onRender, so no data-linking marker nodes
	if (onRender && tag && tag._.noVws) {
		onRender = undefined;
	}
	outerOnRender = onRender;
	if (onRender === true) {
		// Used by view.refresh(). Don't create a new wrapper view.
		outerOnRender = undefined;
		onRender = view._.onRender;
	}
	// Set additional context on views created here, (as modified context inherited from the parent, and to be inherited by child views)
	context = tmpl.helpers
		? extendCtx(tmpl.helpers, context)
		: context;

	newCtx = context;
	if ($isArray(data) && !noIteration) {
		// Create a view for the array, whose child views correspond to each data item. (Note: if key and view are passed in
		// along with parent view, treat as insert -e.g. from view.addViews - so view is already the view item for array)
		newView = swapContent
			? view
			: (key !== undefined && view)
				|| new View(context, "array", view, data, tmpl, key, onRender, contentTmpl);
		newView._.nl= noLinking;
		if (view && view._.useKey) {
			// Parent is not an 'array view'
			newView._.bnd = !tag || tag._.bnd && tag; // For array views that are data bound for collection change events, set the
			// view._.bnd property to true for top-level link() or data-link="{for}", or to the tag instance for a data-bound tag, e.g. {^{for ...}}
			newView.tag = tag;
		}
		for (i = 0, l = data.length; i < l; i++) {
			// Create a view for each data item.
			childView = new View(newCtx, "item", newView, data[i], tmpl, (key || 0) + i, onRender, newView.content);
			if (itemVar) {
				(childView.ctx = $extend({}, newCtx))[itemVar] = $sub._cp(data[i], "#data", childView);
			}
			itemResult = tmpl.fn(data[i], childView, $sub);
			result += newView._.onRender ? newView._.onRender(itemResult, childView) : itemResult;
		}
	} else {
		// Create a view for singleton data object. The type of the view will be the tag name, e.g. "if" or "mytag" except for
		// "item", "array" and "data" views. A "data" view is from programmatic render(object) against a 'singleton'.
		newView = swapContent ? view : new View(newCtx, tmplName || "data", view, data, tmpl, key, onRender, contentTmpl);

		if (itemVar) {
			(newView.ctx = $extend({}, newCtx))[itemVar] = $sub._cp(data, "#data", newView);
		}

		newView.tag = tag;
		newView._.nl = noLinking;
		result += tmpl.fn(data, newView, $sub);
	}
	if (tag) {
		newView.tagElse = tagCtx.index;
		tagCtx.contentView = newView;
	}
	return outerOnRender ? outerOnRender(result, newView) : result;
}

//===========================
// Build and compile template
//===========================

// Generate a reusable function that will serve to render a template against data
// (Compile AST then build template function)

function onRenderError(e, view, fallback) {
	var message = fallback !== undefined
		? $isFunction(fallback)
			? fallback.call(view.data, e, view)
			: fallback || ""
		: "{Error: " + (e.message||e) + "}";

	if ($subSettings.onError && (fallback = $subSettings.onError.call(view.data, e, fallback && message, view)) !== undefined) {
		message = fallback; // There is a settings.debugMode(handler) onError override. Call it, and use return value (if any) to replace message
	}
	return view && !view._lc ? $converters.html(message) : message; // For data-link=\"{... onError=...}"... See onDataLinkedTagChange
}

function error(message) {
	throw new $sub.Err(message);
}

function syntaxError(message) {
	error("Syntax error\n" + message);
}

function tmplFn(markup, tmpl, isLinkExpr, convertBack, hasElse) {
	// Compile markup to AST (abtract syntax tree) then build the template function code from the AST nodes
	// Used for compiling templates, and also by JsViews to build functions for data link expressions

	//==== nested functions ====
	function pushprecedingContent(shift) {
		shift -= loc;
		if (shift) {
			content.push(markup.substr(loc, shift).replace(rNewLine, "\\n"));
		}
	}

	function blockTagCheck(tagName, block) {
		if (tagName) {
			tagName += '}}';
			//			'{{include}} block has {{/for}} with no open {{for}}'
			syntaxError((
				block
					? '{{' + block + '}} block has {{/' + tagName + ' without {{' + tagName
					: 'Unmatched or missing {{/' + tagName) + ', in template:\n' + markup);
		}
	}

	function parseTag(all, bind, tagName, converter, colon, html, codeTag, params, slash, bind2, closeBlock, index) {
/*

     bind     tagName         cvt   cln html code    params            slash   bind2         closeBlk  comment
/(?:{(\^)?{(?:(\w+(?=[\/\s}]))|(\w+)?(:)|(>)|(\*))\s*((?:[^}]|}(?!}))*?)(\/)?|{(\^)?{(?:(?:\/(\w+))\s*|!--[\s\S]*?--))}}/g

(?:
  {(\^)?{            bind
  (?:
    (\w+             tagName
      (?=[\/\s}])
    )
    |
    (\w+)?(:)        converter colon
    |
    (>)              html
    |
    (\*)             codeTag
  )
  \s*
  (                  params
    (?:[^}]|}(?!}))*?
  )
  (\/)?              slash
  |
  {(\^)?{            bind2
  (?:
    (?:\/(\w+))\s*   closeBlock
    |
    !--[\s\S]*?--    comment
  )
)
}}/g

*/
		if (codeTag && bind || slash && !tagName || params && params.slice(-1) === ":" || bind2) {
			syntaxError(all);
		}

		// Build abstract syntax tree (AST): [tagName, converter, params, content, hash, bindings, contentMarkup]
		if (html) {
			colon = ":";
			converter = HTML;
		}
		slash = slash || isLinkExpr && !hasElse;

		var late, openTagName, isLateOb,
			pathBindings = (bind || isLinkExpr) && [[]], // pathBindings is an array of arrays for arg bindings and a hash of arrays for prop bindings
			props = "",
			args = "",
			ctxProps = "",
			paramsArgs = "",
			paramsProps = "",
			paramsCtxProps = "",
			onError = "",
			useTrigger = "",
			// Block tag if not self-closing and not {{:}} or {{>}} (special case) and not a data-link expression
			block = !slash && !colon;

		//==== nested helper function ====
		tagName = tagName || (params = params || "#data", colon); // {{:}} is equivalent to {{:#data}}
		pushprecedingContent(index);
		loc = index + all.length; // location marker - parsed up to here
		if (codeTag) {
			if (allowCode) {
				content.push(["*", "\n" + params.replace(/^:/, "ret+= ").replace(rUnescapeQuotes, "$1") + ";\n"]);
			}
		} else if (tagName) {
			if (tagName === "else") {
				if (rTestElseIf.test(params)) {
					syntaxError('For "{{else if expr}}" use "{{else expr}}"');
				}
				pathBindings = current[9] && [[]];
				current[10] = markup.substring(current[10], index); // contentMarkup for block tag
				openTagName = current[11] || current[0] || syntaxError("Mismatched: " + all);
				// current[0] is tagName, but for {{else}} nodes, current[11] is tagName of preceding open tag
				current = stack.pop();
				content = current[2];
				block = true;
			}
			if (params) {
				// remove newlines from the params string, to avoid compiled code errors for unterminated strings
				parseParams(params.replace(rNewLine, " "), pathBindings, tmpl, isLinkExpr)
					.replace(rBuildHash, function(all, onerror, isCtxPrm, key, keyToken, keyValue, arg, param) {
						if (key === "this:") {
							keyValue = "undefined"; // this=some.path is always a to parameter (one-way), so don't need to compile/evaluate some.path initialization
						}
						if (param) {
							isLateOb = isLateOb || param[0] === "@";
						}
						key = "'" + keyToken + "':";
						if (arg) {
							args += isCtxPrm + keyValue + ",";
							paramsArgs += "'" + param + "',";
						} else if (isCtxPrm) { // Contextual parameter, ~foo=expr
							ctxProps += key + 'j._cp(' + keyValue + ',"' + param + '",view),';
							// Compiled code for evaluating tagCtx on a tag will have: ctx:{'foo':j._cp(compiledExpr, "expr", view)}
							paramsCtxProps += key + "'" + param + "',";
						} else if (onerror) {
							onError += keyValue;
						} else {
							if (keyToken === "trigger") {
								useTrigger += keyValue;
							}
							if (keyToken === "lateRender") {
								late = param !== "false"; // Render after first pass
							}
							props += key + keyValue + ",";
							paramsProps += key + "'" + param + "',";
							hasHandlers = hasHandlers || rHasHandlers.test(keyToken);
						}
						return "";
					}).slice(0, -1);
			}

			if (pathBindings && pathBindings[0]) {
				pathBindings.pop(); // Remove the binding that was prepared for next arg. (There is always an extra one ready).
			}

			newNode = [
					tagName,
					converter || !!convertBack || hasHandlers || "",
					block && [],
					parsedParam(paramsArgs || (tagName === ":" ? "'#data'," : ""), paramsProps, paramsCtxProps), // {{:}} equivalent to {{:#data}}
					parsedParam(args || (tagName === ":" ? "data," : ""), props, ctxProps),
					onError,
					useTrigger,
					late,
					isLateOb,
					pathBindings || 0
				];
			content.push(newNode);
			if (block) {
				stack.push(current);
				current = newNode;
				current[10] = loc; // Store current location of open tag, to be able to add contentMarkup when we reach closing tag
				current[11] = openTagName; // Used for checking syntax (matching close tag)
			}
		} else if (closeBlock) {
			blockTagCheck(closeBlock !== current[0] && closeBlock !== current[11] && closeBlock, current[0]); // Check matching close tag name
			current[10] = markup.substring(current[10], index); // contentMarkup for block tag
			current = stack.pop();
		}
		blockTagCheck(!current && closeBlock);
		content = current[2];
	}
	//==== /end of nested functions ====

	var i, result, newNode, hasHandlers, bindings,
		allowCode = $subSettings.allowCode || tmpl && tmpl.allowCode
			|| $viewsSettings.allowCode === true, // include direct setting of settings.allowCode true for backward compat only
		astTop = [],
		loc = 0,
		stack = [],
		content = astTop,
		current = [,,astTop];

	if (allowCode && tmpl._is) {
		tmpl.allowCode = allowCode;
	}

//TODO	result = tmplFnsCache[markup]; // Only cache if template is not named and markup length < ...,
//and there are no bindings or subtemplates?? Consider standard optimization for data-link="a.b.c"
//		if (result) {
//			tmpl.fn = result;
//		} else {

//		result = markup;
	if (isLinkExpr) {
		if (convertBack !== undefined) {
			markup = markup.slice(0, -convertBack.length - 2) + delimCloseChar0;
		}
		markup = delimOpenChar0 + markup + delimCloseChar1;
	}

	blockTagCheck(stack[0] && stack[0][2].pop()[0]);
	// Build the AST (abstract syntax tree) under astTop
	markup.replace(rTag, parseTag);

	pushprecedingContent(markup.length);

	if (loc = astTop[astTop.length - 1]) {
		blockTagCheck(typeof loc !== STRING && (+loc[10] === loc[10]) && loc[0]);
	}
//			result = tmplFnsCache[markup] = buildCode(astTop, tmpl);
//		}

	if (isLinkExpr) {
		result = buildCode(astTop, markup, isLinkExpr);
		bindings = [];
		i = astTop.length;
		while (i--) {
			bindings.unshift(astTop[i][9]); // With data-link expressions, pathBindings array for tagCtx[i] is astTop[i][9]
		}
		setPaths(result, bindings);
	} else {
		result = buildCode(astTop, tmpl);
	}
	return result;
}

function setPaths(fn, pathsArr) {
	var key, paths,
		i = 0,
		l = pathsArr.length;
	fn.deps = [];
	fn.paths = []; // The array of path binding (array/dictionary)s for each tag/else block's args and props
	for (; i < l; i++) {
		fn.paths.push(paths = pathsArr[i]);
		for (key in paths) {
			if (key !== "_jsvto" && paths.hasOwnProperty(key) && paths[key].length && !paths[key].skp) {
				fn.deps = fn.deps.concat(paths[key]); // deps is the concatenation of the paths arrays for the different bindings
			}
		}
	}
}

function parsedParam(args, props, ctx) {
	return [args.slice(0, -1), props.slice(0, -1), ctx.slice(0, -1)];
}

function paramStructure(paramCode, paramVals) {
	return '\n\tparams:{args:[' + paramCode[0] + '],\n\tprops:{' + paramCode[1] + '}'
		+ (paramCode[2] ? ',\n\tctx:{' + paramCode[2] + '}' : "")
		+ '},\n\targs:[' + paramVals[0] + '],\n\tprops:{' + paramVals[1] + '}'
		+ (paramVals[2] ? ',\n\tctx:{' + paramVals[2] + '}' : "");
}

function parseParams(params, pathBindings, tmpl, isLinkExpr) {

	function parseTokens(all, lftPrn0, lftPrn, bound, path, operator, err, eq, path2, late, prn,
												comma, lftPrn2, apos, quot, rtPrn, rtPrnDot, prn2, space, index, full) {
	// /(\()(?=\s*\()|(?:([([])\s*)?(?:(\^?)(~?[\w$.^]+)?\s*((\+\+|--)|\+|-|~(?![\w$])|&&|\|\||===|!==|==|!=|<=|>=|[<>%*:?\/]|(=))\s*|(!*?(@)?[#~]?[\w$.^]+)([([])?)|(,\s*)|(?:(\()\s*)?\\?(?:(')|("))|(?:\s*(([)\]])(?=[.^]|\s*$|[^([])|[)\]])([([]?))|(\s+)/g,
	//lftPrn0           lftPrn         bound     path               operator     err                                          eq      path2 late            prn      comma  lftPrn2          apos quot        rtPrn  rtPrnDot                  prn2     space
	// (left paren? followed by (path? followed by operator) or (path followed by paren?)) or comma or apos or quot or right paren or space

		function parsePath(allPath, not, object, helper, view, viewProperty, pathTokens, leafToken) {
			// /^(!*?)(?:null|true|false|\d[\d.]*|([\w$]+|\.|~([\w$]+)|#(view|([\w$]+))?)([\w$.^]*?)(?:[.[^]([\w$]+)\]?)?)$/g,
			//    not                               object     helper    view  viewProperty pathTokens      leafToken
			subPath = object === ".";
			if (object) {
				path = path.slice(not.length);
				if (/^\.?constructor$/.test(leafToken||path)) {
					syntaxError(allPath);
				}
				if (!subPath) {
					allPath = (late // late path @a.b.c: not throw on 'property of undefined' if a undefined, and will use _getOb() after linking to resolve late.
							? (isLinkExpr ? '' : '(ltOb.lt=ltOb.lt||') + '(ob='
							: ""
						)
						+ (helper
							? 'view.ctxPrm("' + helper + '")'
							: view
								? "view"
								: "data")
						+ (late
							? ')===undefined' + (isLinkExpr ? '' : ')') + '?"":view._getOb(ob,"'
							: ""
						)
						+ (leafToken
							? (viewProperty
								? "." + viewProperty
								: helper
									? ""
									: (view ? "" : "." + object)
								) + (pathTokens || "")
							: (leafToken = helper ? "" : view ? viewProperty || "" : object, ""));
					allPath = allPath + (leafToken ? "." + leafToken : "");

					allPath = not + (allPath.slice(0, 9) === "view.data"
						? allPath.slice(5) // convert #view.data... to data...
						: allPath)
					+ (late
							? (isLinkExpr ? '"': '",ltOb') + (prn ? ',1)':')')
							: ""
						);
				}
				if (bindings) {
					binds = named === "_linkTo" ? (bindto = pathBindings._jsvto = pathBindings._jsvto || []) : bndCtx.bd;
					if (theOb = subPath && binds[binds.length-1]) {
						if (theOb._cpfn) { // Computed property exprOb
							while (theOb.sb) {
								theOb = theOb.sb;
							}
							if (theOb.prm) {
								if (theOb.bnd) {
									path = "^" + path.slice(1);
								}
								theOb.sb = path;
								theOb.bnd = theOb.bnd || path[0] === "^";
							}
						}
					} else {
						binds.push(path);
					}
					if (prn && !subPath) {
						pathStart[fnDp] = ind;
						compiledPathStart[fnDp] = compiledPath[fnDp].length;
					}
				}
			}
			return allPath;
		}

		//bound = bindings && bound;
		if (bound && !eq) {
			path = bound + path; // e.g. some.fn(...)^some.path - so here path is "^some.path"
		}
		operator = operator || "";
		lftPrn2 = lftPrn2 || "";
		lftPrn = lftPrn || lftPrn0 || lftPrn2;
		path = path || path2;

		if (late && (late = !/\)|]/.test(full[index-1]))) {
			path = path.slice(1).split(".").join("^"); // Late path @z.b.c. Use "^" rather than "." to ensure that deep binding will be used
		}
		// Could do this - but not worth perf cost?? :-
		// if (!path.lastIndexOf("#data.", 0)) { path = path.slice(6); } // If path starts with "#data.", remove that.
		prn = prn || prn2 || "";
		var expr, binds, theOb, newOb, subPath, lftPrnFCall, ret,
			ind = index;

		if (!aposed && !quoted) {
			if (err) {
				syntaxError(params);
			}
			if (rtPrnDot && bindings) {
				// This is a binding to a path in which an object is returned by a helper/data function/expression, e.g. foo()^x.y or (a?b:c)^x.y
				// We create a compiled function to get the object instance (which will be called when the dependent data of the subexpression changes,
				// to return the new object, and trigger re-binding of the subsequent path)
				expr = pathStart[fnDp-1];
				if (full.length - 1 > ind - (expr || 0)) { // We need to compile a subexpression
					expr = $.trim(full.slice(expr, ind + all.length));
					binds = bindto || bndStack[fnDp-1].bd;
					// Insert exprOb object, to be used during binding to return the computed object
					theOb = binds[binds.length-1];
					if (theOb && theOb.prm) {
						while (theOb.sb && theOb.sb.prm) {
							theOb = theOb.sb;
						}
						newOb = theOb.sb = {path: theOb.sb, bnd: theOb.bnd};
					} else {
						binds.push(newOb = {path: binds.pop()}); // Insert exprOb object, to be used during binding to return the computed object
					}
					if (theOb && theOb.sb === newOb) {
						compiledPath[fnDp] = compiledPath[fnDp-1].slice(theOb._cpPthSt) + compiledPath[fnDp];
						compiledPath[fnDp-1] = compiledPath[fnDp-1].slice(0, theOb._cpPthSt);
					}
					newOb._cpPthSt = compiledPathStart[fnDp-1];
					newOb._cpKey = expr;

					compiledPath[fnDp] += full.slice(prevIndex, index);
					prevIndex = index;

					newOb._cpfn = cpFnStore[expr] = cpFnStore[expr] || // Compiled function for computed value: get from store, or compile and store
						new Function("data,view,j", // Compiled function for computed value in template
					"//" + expr + "\nvar v;\nreturn ((v=" + compiledPath[fnDp] + (rtPrn === "]" ? ")]" : rtPrn) + ")!=null?v:null);");

					compiledPath[fnDp-1] += (fnCall[prnDp] && $subSettingsAdvanced.cache ? "view.getCache(\"" + expr.replace(rEscapeQuotes, "\\$&") + "\"" : compiledPath[fnDp]);

					newOb.prm = bndCtx.bd;
					newOb.bnd = newOb.bnd || newOb.path && newOb.path.indexOf("^") >= 0;
				}
				compiledPath[fnDp] = "";
			}
			if (prn === "[") {
				prn = "[j._sq(";
			}
			if (lftPrn === "[") {
				lftPrn = "[j._sq(";
			}
		}
		ret = (aposed
			// within single-quoted string
			? (aposed = !apos, (aposed ? all : lftPrn2 + '"'))
			: quoted
			// within double-quoted string
				? (quoted = !quot, (quoted ? all : lftPrn2 + '"'))
				:
			(
				(lftPrn
					? (
						prnStack[++prnDp] = true,
						prnInd[prnDp] = 0,
						bindings && (
							pathStart[fnDp++] = ind++,
							bndCtx = bndStack[fnDp] = {bd: []},
							compiledPath[fnDp] = "",
							compiledPathStart[fnDp] = 1
						),
						lftPrn) // Left paren, (not a function call paren)
					: "")
				+ (space
					? (prnDp
						? "" // A space within parens or within function call parens, so not a separator for tag args
			// New arg or prop - so insert backspace \b (\x08) as separator for named params, used subsequently by rBuildHash, and prepare new bindings array
						: (paramIndex = full.slice(paramIndex, ind), named
							? (named = boundName = bindto = false, "\b")
							: "\b,") + paramIndex + (paramIndex = ind + all.length, bindings && pathBindings.push(bndCtx.bd = []), "\b")
					)
					: eq
			// named param. Remove bindings for arg and create instead bindings array for prop
						? (fnDp && syntaxError(params), bindings && pathBindings.pop(), named = "_" + path, boundName = bound, paramIndex = ind + all.length,
								bindings && ((bindings = bndCtx.bd = pathBindings[named] = []), bindings.skp = !bound), path + ':')
						: path
			// path
							? (path.split("^").join(".").replace($sub.rPath, parsePath)
								+ (prn || operator)
							)
							: operator
			// operator
								? operator
								: rtPrn
			// function
									? rtPrn === "]" ? ")]" : ")"
									: comma
										? (fnCall[prnDp] || syntaxError(params), ",") // We don't allow top-level literal arrays or objects
										: lftPrn0
											? ""
											: (aposed = apos, quoted = quot, '"')
			))
		);

		if (!aposed && !quoted) {
			if (rtPrn) {
				fnCall[prnDp] = false;
				prnDp--;
			}
		}

		if (bindings) {
			if (!aposed && !quoted) {
				if (rtPrn) {
					if (prnStack[prnDp+1]) {
						bndCtx = bndStack[--fnDp];
						prnStack[prnDp+1] = false;
					}
					prnStart = prnInd[prnDp+1];
				}
				if (prn) {
					prnInd[prnDp+1] = compiledPath[fnDp].length + (lftPrn ? 1 : 0);
					if (path || rtPrn) {
						bndCtx = bndStack[++fnDp] = {bd: []};
						prnStack[prnDp+1] = true;
					}
				}
			}

			compiledPath[fnDp] = (compiledPath[fnDp]||"") + full.slice(prevIndex, index);
			prevIndex = index+all.length;

			if (!aposed && !quoted) {
				if (lftPrnFCall = lftPrn && prnStack[prnDp+1]) {
					compiledPath[fnDp-1] += lftPrn;
					compiledPathStart[fnDp-1]++;
				}
				if (prn === "(" && subPath && !newOb) {
					compiledPath[fnDp] = compiledPath[fnDp-1].slice(prnStart) + compiledPath[fnDp];
					compiledPath[fnDp-1] = compiledPath[fnDp-1].slice(0, prnStart);
				}
			}
			compiledPath[fnDp] += lftPrnFCall ? ret.slice(1) : ret;
		}

		if (!aposed && !quoted && prn) {
			prnDp++;
			if (path && prn === "(") {
				fnCall[prnDp] = true;
			}
		}

		if (!aposed && !quoted && prn2) {
			if (bindings) {
				compiledPath[fnDp] += prn;
			}
			ret += prn;
		}
		return ret;
	}

	var named, bindto, boundName, result,
		quoted, // boolean for string content in double quotes
		aposed, // or in single quotes
		bindings = pathBindings && pathBindings[0], // bindings array for the first arg
		bndCtx = {bd: bindings},
		bndStack = {0: bndCtx},
		paramIndex = 0, // list,
		// The following are used for tracking path parsing including nested paths, such as "a.b(c^d + (e))^f", and chained computed paths such as
		// "a.b().c^d().e.f().g" - which has four chained paths, "a.b()", "^c.d()", ".e.f()" and ".g"
		prnDp = 0,     // For tracking paren depth (not function call parens)
		fnDp = 0,      // For tracking depth of function call parens
		prnInd = {},   // We are in a function call
		prnStart = 0,  // tracks the start of the current path such as c^d() in the above example
		prnStack = {}, // tracks parens which are not function calls, and so are associated with new bndStack contexts
		fnCall = {},   // We are in a function call
		pathStart = {},// tracks the start of the current path such as c^d() in the above example
		compiledPathStart = {0: 0},
		compiledPath = {0:""},
		prevIndex = 0;

	if (params[0] === "@") {
		params = params.replace(rBracketQuote, ".");
	}
	result = (params + (tmpl ? " " : "")).replace($sub.rPrm, parseTokens);

	if (bindings) {
		result = compiledPath[0];
	}

	return !prnDp && result || syntaxError(params); // Syntax error if unbalanced parens in params expression
}

function buildCode(ast, tmpl, isLinkExpr) {
	// Build the template function code from the AST nodes, and set as property on the passed-in template object
	// Used for compiling templates, and also by JsViews to build functions for data link expressions
	var i, node, tagName, converter, tagCtx, hasTag, hasEncoder, getsVal, hasCnvt, useCnvt, tmplBindings, pathBindings, params, boundOnErrStart,
		boundOnErrEnd, tagRender, nestedTmpls, tmplName, nestedTmpl, tagAndElses, content, markup, nextIsElse, oldCode, isElse, isGetVal, tagCtxFn,
		onError, tagStart, trigger, lateRender, retStrOpen, retStrClose,
		tmplBindingKey = 0,
		useViews = $subSettingsAdvanced.useViews || tmpl.useViews || tmpl.tags || tmpl.templates || tmpl.helpers || tmpl.converters,
		code = "",
		tmplOptions = {},
		l = ast.length;

	if (typeof tmpl === STRING) {
		tmplName = isLinkExpr ? 'data-link="' + tmpl.replace(rNewLine, " ").slice(1, -1) + '"' : tmpl;
		tmpl = 0;
	} else {
		tmplName = tmpl.tmplName || "unnamed";
		if (tmpl.allowCode) {
			tmplOptions.allowCode = true;
		}
		if (tmpl.debug) {
			tmplOptions.debug = true;
		}
		tmplBindings = tmpl.bnds;
		nestedTmpls = tmpl.tmpls;
	}
	for (i = 0; i < l; i++) {
		// AST nodes: [0: tagName, 1: converter, 2: content, 3: params, 4: code, 5: onError, 6: trigger, 7:pathBindings, 8: contentMarkup]
		node = ast[i];

		// Add newline for each callout to t() c() etc. and each markup string
		if (typeof node === STRING) {
			// a markup string to be inserted
			code += '+"' + node + '"';
		} else {
			// a compiled tag expression to be inserted
			tagName = node[0];
			if (tagName === "*") {
				// Code tag: {{* }}
				code += ";\n" + node[1] + "\nret=ret";
			} else {
				converter = node[1];
				content = !isLinkExpr && node[2];
				tagCtx = paramStructure(node[3], params = node[4]);
				trigger = node[6];
				lateRender = node[7];
				if (node[8]) { // latePath @a.b.c or @~a.b.c
					retStrOpen = "\nvar ob,ltOb={},ctxs=";
					retStrClose = ";\nctxs.lt=ltOb.lt;\nreturn ctxs;";
				} else {
					retStrOpen = "\nreturn ";
					retStrClose = "";
				}
				markup = node[10] && node[10].replace(rUnescapeQuotes, "$1");
				if (isElse = tagName === "else") {
					if (pathBindings) {
						pathBindings.push(node[9]);
					}
				} else {
					onError = node[5] || $subSettings.debugMode !== false && "undefined"; // If debugMode not false, set default onError handler on tag to "undefined" (see onRenderError)
					if (tmplBindings && (pathBindings = node[9])) { // Array of paths, or false if not data-bound
						pathBindings = [pathBindings];
						tmplBindingKey = tmplBindings.push(1); // Add placeholder in tmplBindings for compiled function
					}
				}
				useViews = useViews || params[1] || params[2] || pathBindings || /view.(?!index)/.test(params[0]);
				// useViews is for perf optimization. For render() we only use views if necessary - for the more advanced scenarios.
				// We use views if there are props, contextual properties or args with #... (other than #index) - but you can force
				// using the full view infrastructure, (and pay a perf price) by opting in: Set useViews: true on the template, manually...
				if (isGetVal = tagName === ":") {
					if (converter) {
						tagName = converter === HTML ? ">" : converter + tagName;
					}
				} else {
					if (content) { // TODO optimize - if content.length === 0 or if there is a tmpl="..." specified - set content to null / don't run this compilation code - since content won't get used!!
						// Create template object for nested template
						nestedTmpl = tmplObject(markup, tmplOptions);
						nestedTmpl.tmplName = tmplName + "/" + tagName;
						// Compile to AST and then to compiled function
						nestedTmpl.useViews = nestedTmpl.useViews || useViews;
						buildCode(content, nestedTmpl);
						useViews = nestedTmpl.useViews;
						nestedTmpls.push(nestedTmpl);
					}

					if (!isElse) {
						// This is not an else tag.
						tagAndElses = tagName;
						useViews = useViews || tagName && (!$tags[tagName] || !$tags[tagName].flow);
						// Switch to a new code string for this bound tag (and its elses, if it has any) - for returning the tagCtxs array
						oldCode = code;
						code = "";
					}
					nextIsElse = ast[i + 1];
					nextIsElse = nextIsElse && nextIsElse[0] === "else";
				}
				tagStart = onError ? ";\ntry{\nret+=" : "\n+";
				boundOnErrStart = "";
				boundOnErrEnd = "";

				if (isGetVal && (pathBindings || trigger || converter && converter !== HTML || lateRender)) {
					// For convertVal we need a compiled function to return the new tagCtx(s)
					tagCtxFn = new Function("data,view,j", "// " + tmplName + " " + (++tmplBindingKey) + " " + tagName
						+ retStrOpen + "{" + tagCtx + "};" + retStrClose);
					tagCtxFn._er = onError;
					tagCtxFn._tag = tagName;
					tagCtxFn._bd = !!pathBindings; // data-linked tag {^{.../}}
					tagCtxFn._lr = lateRender;

					if (isLinkExpr) {
						return tagCtxFn;
					}

					setPaths(tagCtxFn, pathBindings);
					tagRender = 'c("' + converter + '",view,';
					useCnvt = true;
					boundOnErrStart = tagRender + tmplBindingKey + ",";
					boundOnErrEnd = ")";
				}
				code += (isGetVal
					? (isLinkExpr ? (onError ? "try{\n" : "") + "return " : tagStart) + (useCnvt // Call _cnvt if there is a converter: {{cnvt: ... }} or {^{cnvt: ... }}
						? (useCnvt = undefined, useViews = hasCnvt = true, tagRender + (tagCtxFn
							? ((tmplBindings[tmplBindingKey - 1] = tagCtxFn), tmplBindingKey) // Store the compiled tagCtxFn in tmpl.bnds, and pass the key to convertVal()
							: "{" + tagCtx + "}") + ")")
						: tagName === ">"
							? (hasEncoder = true, "h(" + params[0] + ")")
							: (getsVal = true, "((v=" + params[0] + ')!=null?v:' + (isLinkExpr ? 'null)' : '"")'))
							// Non strict equality so data-link="title{:expr}" with expr=null/undefined removes title attribute
					)
					: (hasTag = true, "\n{view:view,content:false,tmpl:" // Add this tagCtx to the compiled code for the tagCtxs to be passed to renderTag()
						+ (content ? nestedTmpls.length : "false") + "," // For block tags, pass in the key (nestedTmpls.length) to the nested content template
						+ tagCtx + "},"));

				if (tagAndElses && !nextIsElse) {
					// This is a data-link expression or an inline tag without any elses, or the last {{else}} of an inline tag
					// We complete the code for returning the tagCtxs array
					code = "[" + code.slice(0, -1) + "]";
					tagRender = 't("' + tagAndElses + '",view,this,';
					if (isLinkExpr || pathBindings) {
						// This is a bound tag (data-link expression or inline bound tag {^{tag ...}}) so we store a compiled tagCtxs function in tmp.bnds
						code = new Function("data,view,j", " // " + tmplName + " " + tmplBindingKey + " " + tagAndElses + retStrOpen + code
							+ retStrClose);
						code._er = onError;
						code._tag = tagAndElses;
						if (pathBindings) {
							setPaths(tmplBindings[tmplBindingKey - 1] = code, pathBindings);
						}
						code._lr = lateRender;
						if (isLinkExpr) {
							return code; // For a data-link expression we return the compiled tagCtxs function
						}
						boundOnErrStart = tagRender + tmplBindingKey + ",undefined,";
						boundOnErrEnd = ")";
					}

					// This is the last {{else}} for an inline tag.
					// For a bound tag, pass the tagCtxs fn lookup key to renderTag.
					// For an unbound tag, include the code directly for evaluating tagCtxs array
					code = oldCode + tagStart + tagRender + (pathBindings && tmplBindingKey || code) + ")";
					pathBindings = 0;
					tagAndElses = 0;
				}
				if (onError && !nextIsElse) {
					useViews = true;
					code += ';\n}catch(e){ret' + (isLinkExpr ? "urn " : "+=") + boundOnErrStart + 'j._err(e,view,' + onError + ')' + boundOnErrEnd + ';}' + (isLinkExpr ? "" : '\nret=ret');
				}
			}
		}
	}
	// Include only the var references that are needed in the code
	code = "// " + tmplName
		+ (tmplOptions.debug ? "\ndebugger;" : "")
		+ "\nvar v"
		+ (hasTag ? ",t=j._tag" : "")                // has tag
		+ (hasCnvt ? ",c=j._cnvt" : "")              // converter
		+ (hasEncoder ? ",h=j._html" : "")           // html converter
		+ (isLinkExpr
				? (node[8] // late @... path?
						? ", ob"
						: ""
					) + ";\n"
				: ',ret=""')
		+ code
		+ (isLinkExpr ? "\n" : ";\nreturn ret;");

	try {
		code = new Function("data,view,j", code);
	} catch (e) {
		syntaxError("Compiled template code:\n\n" + code + '\n: "' + (e.message||e) + '"');
	}
	if (tmpl) {
		tmpl.fn = code;
		tmpl.useViews = !!useViews;
	}
	return code;
}

//==========
// Utilities
//==========

// Merge objects, in particular contexts which inherit from parent contexts
function extendCtx(context, parentContext) {
	// Return copy of parentContext, unless context is defined and is different, in which case return a new merged context
	// If neither context nor parentContext are defined, return undefined
	return context && context !== parentContext
		? (parentContext
			? $extend($extend({}, parentContext), context)
			: context)
		: parentContext && $extend({}, parentContext);
}

function getTargetProps(source, tagCtx) {
	// this pointer is theMap - which has tagCtx.props too
	// arguments: tagCtx.args.
	var key, prop,
		map = tagCtx.map,
		propsArr = map && map.propsArr;

	if (!propsArr) { // map.propsArr is the full array of {key:..., prop:...} objects
		propsArr = [];
		if (typeof source === OBJECT || $isFunction(source)) {
			for (key in source) {
				prop = source[key];
				if (key !== $expando && source.hasOwnProperty(key) && (!tagCtx.props.noFunctions || !$.isFunction(prop))) {
					propsArr.push({key: key, prop: prop});
				}
			}
		}
		if (map) {
			map.propsArr = map.options && propsArr; // If bound {^{props}} and not isRenderCall, store propsArr on map (map.options is defined only for bound, && !isRenderCall)
		}
	}
	return getTargetSorted(propsArr, tagCtx); // Obtains map.tgt, by filtering, sorting and splicing the full propsArr
}

function getTargetSorted(value, tagCtx) {
	// getTgt
	var mapped, start, end,
		tag = tagCtx.tag,
		props = tagCtx.props,
		propParams = tagCtx.params.props,
		filter = props.filter,
		sort = props.sort,
		directSort = sort === true,
		step = parseInt(props.step),
		reverse = props.reverse ? -1 : 1;

	if (!$isArray(value)) {
		return value;
	}
	if (directSort || sort && typeof sort === STRING) {
		// Temporary mapped array holds objects with index and sort-value
		mapped = value.map(function(item, i) {
			item = directSort ? item : getPathObject(item, sort);
			return {i: i, v: typeof item === STRING ? item.toLowerCase() : item};
		});
		// Sort mapped array
		mapped.sort(function(a, b) {
			return a.v > b.v ? reverse : a.v < b.v ? -reverse : 0;
		});
		// Map to new array with resulting order
		value = mapped.map(function(item){
			return value[item.i];
		});
	} else if ((sort || reverse < 0) && !tag.dataMap) {
		value = value.slice(); // Clone array first if not already a new array
	}
	if ($isFunction(sort)) {
		value = value.sort(function() { // Wrap the sort function to provide tagCtx as 'this' pointer
			return sort.apply(tagCtx, arguments);
		});
	}
	if (reverse < 0 && (!sort || $isFunction(sort))) { // Reverse result if not already reversed in sort
		value = value.reverse();
	}

	if (value.filter && filter) { // IE8 does not support filter
		value = value.filter(filter, tagCtx);
		if (tagCtx.tag.onFilter) {
			tagCtx.tag.onFilter(tagCtx);
		}
	}

	if (propParams.sorted) {
		mapped = (sort || reverse < 0) ? value : value.slice();
		if (tag.sorted) {
			$.observable(tag.sorted).refresh(mapped); // Note that this might cause the start and end props to be modified - e.g. by pager tag control
		} else {
			tagCtx.map.sorted = mapped;
		}
	}

	start = props.start; // Get current value - after possible changes triggered by tag.sorted refresh() above
	end = props.end;
	if (propParams.start && start === undefined || propParams.end && end === undefined) {
		start = end = 0;
	}
	if (!isNaN(start) || !isNaN(end)) { // start or end specified, but not the auto-create Number array scenario of {{for start=xxx end=yyy}}
		start = +start || 0;
		end = end === undefined || end > value.length ? value.length : +end;
		value = value.slice(start, end);
	}
	if (step > 1) {
		start = 0;
		end = value.length;
		mapped = [];
		for (; start<end; start+=step) {
			mapped.push(value[start]);
		}
		value = mapped;
	}
	if (propParams.paged && tag.paged) {
		$observable(tag.paged).refresh(value);
	}

	return value;
}

/** Render the template as a string, using the specified data and helpers/context
* $("#tmpl").render()
*
* @param {any}        data
* @param {hash}       [helpersOrContext]
* @param {boolean}    [noIteration]
* @returns {string}   rendered template
*/
function $fnRender(data, context, noIteration) {
	var tmplElem = this.jquery && (this[0] || error('Unknown template')), // Targeted element not found for jQuery template selector such as "#myTmpl"
		tmpl = tmplElem.getAttribute(tmplAttr);

	return renderContent.call(tmpl && $.data(tmplElem)[jsvTmpl] || $templates(tmplElem),
		data, context, noIteration);
}

//========================== Register converters ==========================

function getCharEntity(ch) {
	// Get character entity for HTML, Attribute and optional data encoding
	return charEntities[ch] || (charEntities[ch] = "&#" + ch.charCodeAt(0) + ";");
}

function getCharFromEntity(match, token) {
	// Get character from HTML entity, for optional data unencoding
	return charsFromEntities[token] || "";
}

function htmlEncode(text) {
	// HTML encode: Replace < > & ' " ` etc. by corresponding entities.
	return text != undefined ? rIsHtml.test(text) && ("" + text).replace(rHtmlEncode, getCharEntity) || text : "";
}

function dataEncode(text) {
	// Encode just < > and & - intended for 'safe data' along with {{:}} rather than {{>}}
  return typeof text === STRING ? text.replace(rDataEncode, getCharEntity) : text;
}

function dataUnencode(text) {
  // Unencode just < > and & - intended for 'safe data' along with {{:}} rather than {{>}}
  return  typeof text === STRING ? text.replace(rDataUnencode, getCharFromEntity) : text;
}

//========================== Initialize ==========================

$sub = $views.sub;
$viewsSettings = $views.settings;

if (!(jsr || $ && $.render)) {
	// JsRender/JsViews not already loaded (or loaded without jQuery, and we are now moving from jsrender namespace to jQuery namepace)
	for (jsvStoreName in jsvStores) {
		registerStore(jsvStoreName, jsvStores[jsvStoreName]);
	}

	$converters = $views.converters;
	$helpers = $views.helpers;
	$tags = $views.tags;

	$sub._tg.prototype = {
		baseApply: baseApply,
		cvtArgs: convertArgs,
		bndArgs: convertBoundArgs,
		ctxPrm: contextParameter
	};

	topView = $sub.topView = new View();

	//BROWSER-SPECIFIC CODE
	if ($) {

		////////////////////////////////////////////////////////////////////////////////////////////////
		// jQuery (= $) is loaded

		$.fn.render = $fnRender;
		$expando = $.expando;
		if ($.observable) {
			if (versionNumber !== (versionNumber = $.views.jsviews)) {
				// Different version of jsRender was loaded
				throw "jquery.observable.js requires jsrender.js " + versionNumber;
			}
			$extend($sub, $.views.sub); // jquery.observable.js was loaded before jsrender.js
			$views.map = $.views.map;
		}

	} else {
		////////////////////////////////////////////////////////////////////////////////////////////////
		// jQuery is not loaded.

		$ = {};

		if (setGlobals) {
			global.jsrender = $; // We are loading jsrender.js from a script element, not AMD or CommonJS, so set global
		}

		// Error warning if jsrender.js is used as template engine on Node.js (e.g. Express or Hapi...)
		// Use jsrender-node.js instead...
		$.renderFile = $.__express = $.compile = function() { throw "Node.js: use npm jsrender, or jsrender-node.js"; };

		//END BROWSER-SPECIFIC CODE
		$.isFunction = function(ob) {
			return typeof ob === "function";
		};

		$.isArray = Array.isArray || function(obj) {
			return ({}.toString).call(obj) === "[object Array]";
		};

		$sub._jq = function(jq) { // private method to move from JsRender APIs from jsrender namespace to jQuery namespace
			if (jq !== $) {
				$extend(jq, $); // map over from jsrender namespace to jQuery namespace
				$ = jq;
				$.fn.render = $fnRender;
				delete $.jsrender;
				$expando = $.expando;
			}
		};

		$.jsrender = versionNumber;
	}
	$subSettings = $sub.settings;
	$subSettings.allowCode = false;
	$isFunction = $.isFunction;
	$.render = $render;
	$.views = $views;
	$.templates = $templates = $views.templates;

	for (setting in $subSettings) {
		addSetting(setting);
	}

	/**
	* $.views.settings.debugMode(true)
	* @param {boolean} debugMode
	* @returns {Settings}
	*
	* debugMode = $.views.settings.debugMode()
	* @returns {boolean}
	*/
	($viewsSettings.debugMode = function(debugMode) {
		return debugMode === undefined
			? $subSettings.debugMode
			: (
				$subSettings._clFns && $subSettings._clFns(), // Clear linkExprStore (cached compiled expressions), since debugMode setting affects compilation for expressions
				$subSettings.debugMode = debugMode,
				$subSettings.onError = typeof debugMode === STRING
					? function() { return debugMode; }
					: $isFunction(debugMode)
						? debugMode
						: undefined,
				$viewsSettings);
	})(false); // jshint ignore:line

	$subSettingsAdvanced = $subSettings.advanced = {
		cache: true, // By default use cached values of computed values (Otherwise, set advanced cache setting to false)
		useViews: false,
		_jsv: false // For global access to JsViews store
	};

	//========================== Register tags ==========================

	$tags({
		"if": {
			render: function(val) {
				// This function is called once for {{if}} and once for each {{else}}.
				// We will use the tag.rendering object for carrying rendering state across the calls.
				// If not done (a previous block has not been rendered), look at expression for this block and render the block if expression is truthy
				// Otherwise return ""
				var self = this,
					tagCtx = self.tagCtx,
					ret = (self.rendering.done || !val && (tagCtx.args.length || !tagCtx.index))
						? ""
						: (self.rendering.done = true,
							self.selected = tagCtx.index,
							undefined); // Test is satisfied, so render content on current context
				return ret;
			},
			contentCtx: true, // Inherit parent view data context
			flow: true
		},
		"for": {
			sortDataMap: dataMap(getTargetSorted),
			init: function(val, cloned) {
				this.setDataMap(this.tagCtxs);
			},
			render: function(val) {
				// This function is called once for {{for}} and once for each {{else}}.
				// We will use the tag.rendering object for carrying rendering state across the calls.
				var value, filter, srtField, isArray, i, sorted, end, step,
					self = this,
					tagCtx = self.tagCtx,
					range = tagCtx.argDefault === false,
					props = tagCtx.props,
					iterate = range || tagCtx.args.length, // Not final else and not auto-create range
					result = "",
					done = 0;

				if (!self.rendering.done) {
					value = iterate ? val : tagCtx.view.data; // For the final else, defaults to current data without iteration.

					if (range) {
						range = props.reverse ? "unshift" : "push";
						end = +props.end;
						step = +props.step || 1;
						value = []; // auto-create integer array scenario of {{for start=xxx end=yyy}}
						for (i = +props.start || 0; (end - i) * step > 0; i += step) {
							value[range](i);
						}
					}
					if (value !== undefined) {
						isArray = $isArray(value);
						result += tagCtx.render(value, !iterate || props.noIteration);
						// Iterates if data is an array, except on final else - or if noIteration property
						// set to true. (Use {{include}} to compose templates without array iteration)
						done += isArray ? value.length : 1;
					}
					if (self.rendering.done = done) {
						self.selected = tagCtx.index;
					}
					// If nothing was rendered we will look at the next {{else}}. Otherwise, we are done.
				}
				return result;
			},
			setDataMap: function(tagCtxs) {
				var tagCtx, props, paramsProps,
					self = this,
					l = tagCtxs.length;
				while (l--) {
					tagCtx = tagCtxs[l];
					props = tagCtx.props;
					paramsProps = tagCtx.params.props;
					tagCtx.argDefault = props.end === undefined || tagCtx.args.length > 0; // Default to #data except for auto-create range scenario {{for start=xxx end=yyy step=zzz}}
					props.dataMap = (tagCtx.argDefault !== false && $isArray(tagCtx.args[0]) &&
						(paramsProps.sort || paramsProps.start || paramsProps.end || paramsProps.step || paramsProps.filter || paramsProps.reverse
						|| props.sort || props.start || props.end || props.step || props.filter || props.reverse))
						&& self.sortDataMap;
				}
			},
			flow: true
		},
		props: {
			baseTag: "for",
			dataMap: dataMap(getTargetProps),
			init: noop, // Don't execute the base init() of the "for" tag
			flow: true
		},
		include: {
			flow: true
		},
		"*": {
			// {{* code... }} - Ignored if template.allowCode and $.views.settings.allowCode are false. Otherwise include code in compiled template
			render: retVal,
			flow: true
		},
		":*": {
			// {{:* returnedExpression }} - Ignored if template.allowCode and $.views.settings.allowCode are false. Otherwise include code in compiled template
			render: retVal,
			flow: true
		},
		dbg: $helpers.dbg = $converters.dbg = dbgBreak // Register {{dbg/}}, {{dbg:...}} and ~dbg() to throw and catch, as breakpoints for debugging.
	});

	$converters({
		html: htmlEncode,
		attr: htmlEncode, // Includes > encoding since rConvertMarkers in JsViews does not skip > characters in attribute strings
		encode: dataEncode,
		unencode: dataUnencode, // Includes > encoding since rConvertMarkers in JsViews does not skip > characters in attribute strings
		url: function(text) {
			// URL encoding helper.
			return text != undefined ? encodeURI("" + text) : text === null ? text : ""; // null returns null, e.g. to remove attribute. undefined returns ""
		}
	});
}
//========================== Define default delimiters ==========================
$subSettings = $sub.settings;
$isArray = ($||jsr).isArray;
$viewsSettings.delimiters("{{", "}}", "^");

if (jsrToJq) { // Moving from jsrender namespace to jQuery namepace - copy over the stored items (templates, converters, helpers...)
	jsr.views.sub._jq($);
}
return $ || jsr;
}, window));


/***/ }),

/***/ "./resources/assets/front/js/front-language.js":
/*!*****************************************************!*\
  !*** ./resources/assets/front/js/front-language.js ***!
  \*****************************************************/
/***/ (() => {

listenClick('.languageSelection', function () {
  var languageName = $(this).data('prefix-value');
  $.ajax({
    type: 'POST',
    url: route('front.change.language'),
    data: {
      '_token': csrfToken,
      languageName: languageName
    },
    success: function success() {
      location.reload();
    }
  });
});

/***/ }),

/***/ "./resources/assets/front/scss/about.scss":
/*!************************************************!*\
  !*** ./resources/assets/front/scss/about.scss ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/front/scss/front-custom.scss":
/*!*******************************************************!*\
  !*** ./resources/assets/front/scss/front-custom.scss ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/front/scss/main.scss":
/*!***********************************************!*\
  !*** ./resources/assets/front/scss/main.scss ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/js/appointments/appointments.js":
/*!**********************************************************!*\
  !*** ./resources/assets/js/appointments/appointments.js ***!
  \**********************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadAppointmentFilterDate)

var appointmentFilterDate = $("#appointmentDateFilter");
var appointmentStart = moment().startOf("week");
var appointmentEnd = moment().endOf("week");
Livewire.hook("element.init", function () {
  loadAppointmentFilterDate();
  if ($("#paymentType").length) {
    $("#paymentType").select2();
  }
  if ($("#paymentStatus").length) {
    $("#paymentStatus").select2();
  }
  if ($("#doctorApptPaymentStatus").length) {
    $("#doctorApptPaymentStatus").select2();
  }
  if ($("#appointmentStatus").length) {
    $("#appointmentStatus").select2();
  }
  if (appointmentStart != undefined && appointmentEnd != undefined) {
    cb(appointmentStart, appointmentEnd);
  }
});
function loadAppointmentFilterDate() {
  if (!$("#appointmentDateFilter").length) {
    return;
  }

  // let appointmentStart = moment().startOf("week");
  // let appointmentEnd = moment().endOf("week");

  $("#appointmentDateFilter").daterangepicker({
    startDate: appointmentStart,
    endDate: appointmentEnd,
    opens: "left",
    showDropdowns: true,
    locale: {
      format: "DD/MM/YYYY",
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  // cb
  );

  // cb(appointmentStart, appointmentEnd);

  $("#appointmentDateFilter").on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    Livewire.dispatch("changeStatusFilter", {
      status: $("#appointmentStatus").val()
    });
    Livewire.dispatch("changePaymentTypeFilter", {
      type: $("#paymentStatus").val()
    });
    appointmentStart = picker.startDate;
    appointmentEnd = picker.endDate;
  });
}
function cb(start, end) {
  $("#appointmentDateFilter").val(start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY"));
}
listenClick("#appointmentResetFilter", function () {
  $("#paymentStatus").val(0).trigger("change");
  $("#appointmentStatus").val(1).trigger("change");
  var date = moment().startOf("week").format("DD/MM/YYYY") + " - " + moment().endOf("week").format("DD/MM/YYYY");
  $("#appointmentDateFilter").val(date);
  Livewire.dispatch("changeDateFilter", {
    date: date
  });
  hideDropdownManually($("#apptmentFilterBtn"), $(".dropdown-menu"));
});
listenClick("#doctorApptResetFilter", function () {
  $("#doctorApptPaymentStatus").val(1).trigger("change");
  var date = moment().startOf("week").format("DD/MM/YYYY") + " - " + moment().endOf("week").format("DD/MM/YYYY");
  $("#appointmentDateFilter").val(date);
  Livewire.dispatch("changeDateFilter", {
    date: date
  });
  hideDropdownManually($("#doctorAptFilterBtn"), $(".dropdown-menu"));
});
listenClick(".appointment-delete-btn", function (event) {
  var recordId = $(event.currentTarget).attr("data-id");
  deleteItem(route("appointments.destroy", recordId), Lang.get("js.appointment"));
});
listenChange(".appointment-status-change", function () {
  var appointmentStatus = $(this).val();
  var appointmentId = $(this).attr("data-id");
  var currentData = $(this);
  $.ajax({
    url: route("change-status", appointmentId),
    type: "POST",
    data: {
      appointmentId: appointmentId,
      appointmentStatus: appointmentStatus
    },
    _success: function _success(result) {
      $(currentData).children("option.booked").addClass("hide");
      window.location.reload();
      // Turbo.visit(window.location.href);
      displaySuccessMessage(result.message);
    },
    get success() {
      return this._success;
    },
    set success(value) {
      this._success = value;
    }
  });
});
listenChange(".appointment-change-payment-status", function () {
  var paymentStatus = $(this).val();
  var appointmentId = $(this).attr("data-id");
  $("#paymentStatusModal").modal("show").appendTo("body");
  $("#appointmentPaymentStatus").val(paymentStatus);
  $("#appointmentId").val(appointmentId);
});
listenChange("#paymentStatus", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $("#appointmentDateFilter").val()
  });
  Livewire.dispatch("changeStatusFilter", {
    status: $("#appointmentStatus").val()
  });
  Livewire.dispatch("changePaymentTypeFilter", {
    type: $(this).val()
  });
});
listenChange("#doctorApptPaymentStatus", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $("#appointmentDateFilter").val()
  });
  Livewire.dispatch("changeDoctorStatusFilter", {
    status: $(this).val()
  });
});
listenChange("#appointmentStatus", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $("#appointmentDateFilter").val()
  });
  Livewire.dispatch("changeStatusFilter", {
    status: $(this).val()
  });
  Livewire.dispatch("changePaymentTypeFilter", {
    type: $("#paymentStatus").val()
  });
});
listenSubmit("#appointmentPaymentStatusForm", function (event) {
  event.preventDefault();
  var paymentStatus = $("#appointmentPaymentStatus").val();
  var appointmentId = $("#appointmentId").val();
  var paymentMethod = $("#paymentType").val();
  $.ajax({
    url: route("change-payment-status", appointmentId),
    type: "POST",
    data: {
      appointmentId: appointmentId,
      paymentStatus: paymentStatus,
      paymentMethod: paymentMethod,
      loginUserId: currentLoginUserId
    },
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $("#paymentStatusModal").modal("hide");
        location.reload();
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/appointments/calendar.js":
/*!******************************************************!*\
  !*** ./resources/assets/js/appointments/calendar.js ***!
  \******************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadAppointmentCalendar);
var popover;
var popoverState = false;
var appointmentStatusId = null;
var calendar;
var data = {
  id: '',
  uId: '',
  eventName: '',
  patientName: '',
  eventDescription: '',
  eventStatus: '',
  startDate: '',
  endDate: '',
  amount: 0,
  service: '',
  doctorName: ''
};

// View event variables
var viewEventName, viewEventDescription, viewEventStatus, viewStartDate, viewPatientName, viewEndDate, viewModal, viewEditButton, viewDeleteButton, viewService, viewUId, viewAmount;
function loadAppointmentCalendar() {
  initCalendarApp();
  init();
}
var initCalendarApp = function initCalendarApp() {
  if (!$('#adminAppointmentCalendar').length) {
    return;
  }
  if (usersRole == 'patient') {
    return;
  }
  var calendarEl = document.getElementById('adminAppointmentCalendar');
  var lang = $('.currentLanguage').val();
  calendar = new FullCalendar.Calendar(calendarEl, {
    locale: lang,
    themeSystem: 'bootstrap5',
    height: 750,
    buttonText: {
      today: Lang.get('js.today'),
      day: Lang.get('js.day'),
      month: Lang.get('js.month')
    },
    headerToolbar: {
      left: 'title',
      center: 'prev,next today',
      right: 'dayGridDay,dayGridMonth'
    },
    initialDate: new Date(),
    timeZone: 'UTC',
    dayMaxEvents: true,
    events: function events(info, successCallback, failureCallback) {
      $.ajax({
        url: route('appointments.calendar'),
        type: 'GET',
        data: info,
        success: function success(result) {
          if (result.success) {
            successCallback(result.data);
          }
        },
        error: function error(result) {
          displayErrorMessage(result.responseJSON.message);
          failureCallback();
        }
      });
    },
    // MouseEnter event --- more info: https://fullcalendar.io/docs/eventMouseEnter
    eventMouseEnter: function eventMouseEnter(arg) {
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        patient: arg.event.extendedProps.patient,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        doctorName: arg.event.extendedProps.doctorName
      });

      // Show popover preview
      initPopovers(arg.el);
    },
    eventMouseLeave: function eventMouseLeave() {
      hidePopovers();
    },
    // Click event --- more info: https://fullcalendar.io/docs/eventClick
    eventClick: function eventClick(arg) {
      hidePopovers();
      appointmentStatusId = arg.event.id;
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        patient: arg.event.extendedProps.patient,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        doctorName: arg.event.extendedProps.doctorName
      });
      handleViewEvent();
    }
  });
  calendar.render();
};
var init = function init() {
  if (!$('#eventModal').length) {
    return;
  }
  var viewElement = document.getElementById('eventModal');
  viewModal = new bootstrap.Modal(viewElement);
  viewEventName = viewElement.querySelector('[data-calendar="event_name"]');
  viewPatientName = viewElement.querySelector('[data-calendar="event_patient_name"]');
  viewEventDescription = viewElement.querySelector('[data-calendar="event_description"]');
  viewEventStatus = viewElement.querySelector('[data-calendar="event_status"]');
  viewAmount = viewElement.querySelector('[data-calendar="event_amount"]');
  viewUId = viewElement.querySelector('[data-calendar="event_uId"]');
  viewService = viewElement.querySelector('[data-calendar="event_service"]');
  viewStartDate = viewElement.querySelector('[data-calendar="event_start_date"]');
  viewEndDate = viewElement.querySelector('[data-calendar="event_end_date"]');
};

// Format FullCalendar responses
var formatArgs = function formatArgs(res) {
  data.id = res.id;
  data.eventName = res.title;
  data.patientName = res.patient;
  data.eventDescription = res.description;
  data.eventStatus = res.status;
  data.startDate = res.startStr;
  data.endDate = res.endStr;
  data.amount = res.amount;
  data.uId = res.uId;
  data.service = res.service;
  data.doctorName = res.doctorName;
};

// Initialize popovers --- more info: https://getbootstrap.com/docs/4.0/components/popovers/
var initPopovers = function initPopovers(element) {
  hidePopovers();

  // Generate popover content
  var startDate = data.allDay ? moment(data.startDate).format('Do MMM, YYYY') : moment(data.startDate).format('Do MMM, YYYY - h:mm a');
  var endDate = data.allDay ? moment(data.endDate).format('Do MMM, YYYY') : moment(data.endDate).format('Do MMM, YYYY - h:mm a');
  var popoverHtml = '<div class="fw-bolder mb-2"><b>Doctor</b>: ' + data.doctorName + '<div class="fw-bolder mb-2"><b>Patient</b>: ' + data.patientName + '</div><div class="fs-7"><span class="fw-bold">Start:</span> ' + startDate + '</div><div class="fs-7 mb-4"><span class="fw-bold">End:</span> ' + endDate + '</div>';

  // Popover options
  var options = {
    container: 'body',
    trigger: 'manual',
    boundary: 'window',
    placement: 'auto',
    dismiss: true,
    html: true,
    title: 'Appointment Details',
    content: popoverHtml
  };
};

// Hide active popovers
var hidePopovers = function hidePopovers() {
  if (popoverState) {
    popover.dispose();
    popoverState = false;
  }
};

// Handle view event
var handleViewEvent = function handleViewEvent() {
  $('.fc-popover').addClass('hide');
  viewModal.show();

  // Detect all day event
  var eventNameMod;
  var startDateMod;
  var endDateMod;
  var book = $('#bookCalenderConst').val();
  var checkIn = $('#checkInCalenderConst').val();
  var checkOut = $('#checkOutCalenderConst').val();
  var cancel = $('#cancelCalenderConst').val();
  eventNameMod = '';
  startDateMod = moment(data.startDate).utc().format("DD MMM, YYYY - h:mm A");
  endDateMod = moment(data.endDate).utc().format('DD MMM, YYYY - h:mm A');
  viewEndDate.innerText = ': ' + endDateMod;
  viewStartDate.innerText = ': ' + startDateMod;

  // Populate view data
  viewEventName.innerText = Lang.get('js.doctor') + data.doctorName;
  viewPatientName.innerText = Lang.get('js.patient') + data.patientName;
  $(viewEventStatus).empty();
  $(viewEventStatus).append("\n<option class=\"booked\" disabled value=\"".concat(book, "\" ").concat(data.eventStatus == book ? 'selected' : '', ">").concat(Lang.get('js.booked'), "</option>\n<option value=\"").concat(checkIn, "\" ").concat(data.eventStatus == checkIn ? 'selected' : '', " ").concat(data.eventStatus == checkIn ? 'selected' : '', "\n    ").concat(data.eventStatus == cancel || data.eventStatus == checkOut ? 'disabled' : '', ">").concat(Lang.get('js.check_in'), "</option>\n<option value=\"").concat(checkOut, "\" ").concat(data.eventStatus == checkOut ? 'selected' : '', "\n    ").concat(data.eventStatus == cancel || data.eventStatus == book ? 'disabled' : '', ">").concat(Lang.get('js.check_out'), "</option>\n<option value=\"").concat(cancel, "\" ").concat(data.eventStatus == cancel ? 'selected' : '', " ").concat(data.eventStatus == checkIn ? 'disabled' : '', "\n   ").concat(data.eventStatus == checkOut ? 'disabled' : '', ">").concat(Lang.get('js.cancelled'), "</option>\n"));
  $(viewEventStatus).val(data.eventStatus).trigger('change');
  viewAmount.innerText = addCommas(data.amount);
  viewUId.innerText = data.uId;
  viewService.innerText = data.service;
};
listenChange('#changeAppointmentStatus', function () {
  if (!$(this).val()) {
    return false;
  }
  var appointmentStatus = $(this).val();
  var appointmentId = appointmentStatusId;
  if (parseInt(appointmentStatus) === data.eventStatus) {
    return false;
  }
  $.ajax({
    url: route('change-status', appointmentId),
    type: 'POST',
    data: {
      appointmentId: appointmentId,
      appointmentStatus: appointmentStatus
    },
    success: function success(result) {
      displaySuccessMessage(result.message);
      $('#eventModal').modal('hide');
      calendar.refetchEvents();
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/appointments/create-edit.js":
/*!*********************************************************!*\
  !*** ./resources/assets/js/appointments/create-edit.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr/dist/l10n */ "./node_modules/flatpickr/dist/l10n/index.js");
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__);

document.addEventListener('DOMContentLoaded', loadAppointmentCreateEditData);
var appointmentDate = $('#appointmentDate');
var selectedDate;
var selectedSlotTime;
var timezoneOffsetMinutes = new Date().getTimezoneOffset();
timezoneOffsetMinutes = timezoneOffsetMinutes === 0 ? 0 : -timezoneOffsetMinutes;
function loadAppointmentCreateEditData() {
  if (!$('#appointmentDate').length) {
    return;
  }
  var lang = $('.currentLanguage').val();
  $('#appointmentDate').flatpickr({
    "locale": lang,
    minDate: new Date(),
    disableMobile: true
  });
  $('.no-time-slot').removeClass('d-none');
}
listenChange('#appointmentDate', function () {
  selectedDate = $(this).val();
  var userRole = $('#userRole').val();
  var doctorRole = $('#doctorRole').val();
  var appointmentIsEdit = $('#appointmentIsEdit').val();
  $('.appointment-slot-data').html('');
  var url = '';
  if (!isEmpty(userRole) || !isEmpty(doctorRole)) {
    if (!isEmpty(userRole)) {
      url = route('patients.doctor-session-time');
    }
    if (!isEmpty(doctorRole)) {
      url = route('doctors.doctor-session-time');
    }
  } else {
    url = route('doctor-session-time');
  }
  // let url = !isEmpty(userRole)
  //     ? route('patients.doctor-session-time')
  //     : route('doctor-session-time');
  $.ajax({
    url: url,
    type: 'GET',
    data: {
      'adminAppointmentDoctorId': $('#adminAppointmentDoctorId').val(),
      'date': selectedDate,
      'timezone_offset_minutes': timezoneOffsetMinutes
    },
    success: function success(result) {
      if (result.success) {
        if (result.data['bookedSlot'] != null && result.data['bookedSlot'].length > 0) {
          if (result.data['slots'].length == 0) {
            $('.no-time-slot').addClass('d-none');
            $('.doctor-time-over').removeClass('d-none');
          }
        }
        $.each(result.data['slots'], function (index, value) {
          if (appointmentIsEdit && fromTime == value) {
            $('.no-time-slot').addClass('d-none');
            $('.doctor-time-over').addClass('d-none');
            $('.appointment-slot-data').append('<span class="time-slot col-lg-2  activeSlot" data-id="' + value + '">' + value + '</span>');
          } else {
            $('.no-time-slot').addClass('d-none');
            $('.doctor-time-over').addClass('d-none');
            if (result.data['bookedSlot'] == null) {
              $('.appointment-slot-data').append('<span class="time-slot col-lg-2" data-id="' + value + '">' + value + '</span>');
            } else {
              if ($.inArray(value, result.data['bookedSlot']) !== -1) {
                $('.appointment-slot-data').append('<span class="time-slot col-lg-2 bookedSlot " data-id="' + value + '">' + value + '</span>');
              } else {
                $('.appointment-slot-data').append('<span class="time-slot col-lg-2" data-id="' + value + '">' + value + '</span>');
              }
            }
          }
        });
      }
    },
    error: function error(result) {
      $('.no-time-slot').removeClass('d-none');
      $('.doctor-time-over').addClass('d-none');
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('.time-slot', function () {
  if ($('.time-slot').hasClass('activeSlot')) {
    $('.time-slot').removeClass('activeSlot');
    selectedSlotTime = $(this).addClass('activeSlot');
  } else {
    selectedSlotTime = $(this).addClass('activeSlot');
  }
  var fromToTime = $(this).attr('data-id').split('-');
  var fromTime = fromToTime[0];
  var toTime = fromToTime[1];
  $('#timeSlot').val('');
  $('#toTime').val('');
  $('#timeSlot').val(fromTime);
  $('#toTime').val(toTime);
});
var charge;
var addFees = parseInt($('#addFees').val());
var totalFees;
listenChange('#adminAppointmentDoctorId', function () {
  $('#chargeId').val('');
  $('#payableAmount').val('');
  appointmentDate.val('');
  $('#addFees').val('');
  $('.appointment-slot-data').html('');
  $('.no-time-slot').removeClass('d-none');
  var url = !isEmpty(userRole) ? route('patients.get-service') : route('get-service');
  $.ajax({
    url: url,
    type: 'GET',
    data: {
      'appointmentDoctorId': $(this).val()
    },
    success: function success(result) {
      if (result.success) {
        $('#appointmentDate').removeAttr('disabled');
        $('#appointmentServiceId').empty();
        $('#appointmentServiceId').append($('<option value=""></option>').text(Lang.get('js.select_service')));
        $.each(result.data, function (i, v) {
          $('#appointmentServiceId').append($('<option></option>').attr('value', v.id).text(v.name));
        });
      }
    }
  });
});
listenChange('#appointmentServiceId', function () {
  var url = !isEmpty(userRole) ? route('patients.get-charge') : route('get-charge');
  $.ajax({
    url: url,
    type: 'GET',
    data: {
      'chargeId': $(this).val()
    },
    success: function success(result) {
      if (result.success) {
        $('#chargeId').val('');
        $('#addFees').val('');
        $('#payableAmount').val('');
        if (result.data) {
          $('#chargeId').val(result.data.charges);
          $('#payableAmount').val(result.data.charges);
          charge = result.data.charges;
        }
      }
    }
  });
});
listenKeyup('#addFees', function (e) {
  if (e.which != 8 && isNaN(String.fromCharCode(e.which))) {
    e.preventDefault();
  }
  totalFees = '';
  totalFees = parseFloat(charge) + parseFloat($(this).val() ? $(this).val() : 0);
  $('#payableAmount').val(totalFees.toFixed(2));
});
listenSubmit('#addAppointmentForm', function (e) {
  e.preventDefault();
  var data = new FormData($(this)[0]);
  $('.submitAppointmentBtn').prop(Lang.get('js.discard'), true);
  $('.submitAppointmentBtn').text(Lang.get('js.please_wait'));
  $.ajax({
    url: $(this).attr('action'),
    type: 'POST',
    data: data,
    processData: false,
    contentType: false,
    success: function success(mainResult) {
      if (mainResult.success) {
        var appID = mainResult.data.appointmentId;

        //displaySuccessMessage(mainResult.message);

        $('#addAppointmentForm')[0].reset();
        $('#addAppointmentForm').val('').trigger('change');
        if (mainResult.data.payment_type == $('#paytmMethod').val()) {
          window.location.replace(route('paytm.init', {
            'appointmentId': appID
          }));
        }
        if (mainResult.data.payment_type == $('#authorizeMethod').val()) {
          window.location.href = route('authorize.init', {
            appointmentId: appID
          });

          // Turbo.visit(route('authorize.init', {'appointmentId': appID}));
        }
        if (mainResult.data.payment_type == $('#paypalMethod').val()) {
          $.ajax({
            type: 'GET',
            url: route('paypal.init'),
            data: {
              'appointmentId': appID
            },
            success: function success(result) {
              if (result.status == 200) {
                if (result.link != null) {
                  location.href = result.link;
                }
              } else {
                displayErrorMessage(result.message);
              }
            },
            error: function error(result) {
              displayErrorMessage(result.responseJSON.message);
            }
          });
        }
        if (mainResult.data.payment_type == $('#manuallyMethod').val()) {
          window.location.replace(route('manually-payment', {
            'appointmentId': appID
          }));
        }
        if (mainResult.data.payment_type == $('#stripeMethod').val()) {
          var sessionId = mainResult.data[0].sessionId;
          stripe.redirectToCheckout({
            sessionId: sessionId
          }).then(function (mainResult) {
            manageAjaxErrors(mainResult);
          });
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
      $('.submitAppointmentBtn').prop(Lang.get('js.discard'), false);
      $('.submitAppointmentBtn').text(Lang.get('js.save'));
    },
    complete: function complete() {}
  });
});

/***/ }),

/***/ "./resources/assets/js/appointments/patient-appointments.js":
/*!******************************************************************!*\
  !*** ./resources/assets/js/appointments/patient-appointments.js ***!
  \******************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded',
//     loadPatientPanelAppointmentFilterData)
var patientPanelApptmentStart = moment().startOf("week");
var patientPanelApptmentEnd = moment().endOf("week");
function loadPatientPanelAppointmentFilterData() {
  if (!$("#patientAppointmentDate").length) {
    return;
  }

  // let patientPanelApptmentStart = moment().startOf("week");
  // let patientPanelApptmentEnd = moment().endOf("week");

  var patientDatePicker = $("#patientAppointmentDate").daterangepicker({
    startDate: patientPanelApptmentStart,
    endDate: patientPanelApptmentEnd,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  // cb
  );

  // cb(patientPanelApptmentStart, patientPanelApptmentEnd);
  patientDatePicker.on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    patientPanelApptmentStart = picker.startDate;
    patientPanelApptmentEnd = picker.endDate;

    // Livewire.dispatch("changeDateFilter", { date: $(this).val() });
  });
}
function cb(start, end) {
  $("#patientAppointmentDate").val(start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"));
}
listenClick("#patientPanelApptmentResetFilter", function () {
  Livewire.dispatch("refresh");
  $("#patientPaymentStatus").val(0).trigger("change");
  $("#patientAppointmentStatus").val(1).trigger("change");
  $("#patientAppointmentDate").data("daterangepicker").setStartDate(moment().startOf("week").format("MM/DD/YYYY"));
  $("#patientAppointmentDate").data("daterangepicker").setEndDate(moment().endOf("week").format("MM/DD/YYYY"));
  hideDropdownManually($("#patientPanelApptFilterBtn"), $(".dropdown-menu"));
});
listenChange("#patientPaymentStatus", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $("#patientAppointmentDate").val()
  });
  Livewire.dispatch("changePaymentTypeFilter", {
    type: $(this).val()
  });
});
listenChange("#patientAppointmentStatus", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $("#patientAppointmentDate").val()
  });
  Livewire.dispatch("changeStatusFilter", {
    status: $(this).val()
  });
});

// document.addEventListener('livewire:load', function () {
//     window.livewire.hook('message.processed', () => {
//         if ($('#patientPaymentStatus').length) {
//             $('#patientPaymentStatus').select2()
//         }
//         if ($('#patientAppointmentStatus').length) {
//             $('#patientAppointmentStatus').select2()
//         }
//     })
// })

Livewire.hook("element.init", function () {
  loadPatientPanelAppointmentFilterData();
  if ($("#patientPaymentStatus").length) {
    $("#patientPaymentStatus").select2();
  }
  if ($("#patientAppointmentStatus").length) {
    $("#patientAppointmentStatus").select2();
  }
  if (patientPanelApptmentStart != undefined && patientPanelApptmentEnd != undefined) {
    cb(patientPanelApptmentStart, patientPanelApptmentEnd);
  }
});
listenClick(".patient-panel-apptment-delete-btn", function (event) {
  var userRole = $("#userRole").val();
  var patientPanelApptmentRecordId = $(event.currentTarget).attr("data-id");
  var patientPanelApptmentRecordUrl = !isEmpty(userRole) ? route("patients.appointments.destroy", patientPanelApptmentRecordId) : route("appointments.destroy", patientPanelApptmentRecordId);
  deleteItem(patientPanelApptmentRecordUrl, "Appointment");
});
listenClick(".patient-cancel-appointment", function (event) {
  var appointmentId = $(event.currentTarget).attr("data-id");
  cancelAppointment(route("patients.cancel-status"), Lang.get("js.appointment"), appointmentId);
});
window.cancelAppointment = function (url, header, appointmentId) {
  swal({
    title: Lang.get("js.cancelled_appointment"),
    text: Lang.get("js.are_you_sure_cancel") + header + " ?",
    type: "warning",
    icon: "warning",
    showCancelButton: true,
    closeOnConfirm: false,
    confirmButtonColor: "#266CB0",
    showLoaderOnConfirm: true,
    buttons: {
      confirm: Lang.get("js.yes"),
      cancel: Lang.get("js.no")
    }
  }).then(function (result) {
    if (result) {
      deleteItemAjax(url, header, appointmentId);
    }
  });
};
function deleteItemAjax(url, header, appointmentId) {
  $.ajax({
    url: route("patients.cancel-status"),
    type: "POST",
    data: {
      appointmentId: appointmentId
    },
    success: function success(obj) {
      if (obj.success) {
        Livewire.dispatch("refresh");
      }
      swal({
        title: Lang.get("js.cancelled_appointment"),
        text: header + Lang.get("js.has_cancel"),
        icon: "success",
        confirmButtonColor: "#266CB0",
        timer: 2000
      });
    },
    error: function error(data) {
      swal({
        title: "Error",
        icon: "error",
        text: data.responseJSON.message,
        type: "error",
        confirmButtonColor: "#266CB0",
        timer: 5000
      });
    }
  });
}
listenClick("#submitBtn", function (event) {
  event.preventDefault();
  var paymentGatewayType = $("#paymentGatewayType").val();
  if (isEmpty(paymentGatewayType)) {
    displayErrorMessage(Lang.get("js.select_payment"));
    return false;
  }
  var stripeMethod = 2;
  var paypalMethod = 4;
  var authorizeMethod = 6;
  var paytmMethod = 7;
  var appointmentId = $("#patientAppointmentId").val();
  var btnSubmitEle = $("#patientPaymentForm").find("#submitBtn");
  setAdminBtnLoader(btnSubmitEle);
  if (paymentGatewayType == stripeMethod) {
    $.ajax({
      url: route("patients.appointment-payment"),
      type: "POST",
      data: {
        appointmentId: appointmentId
      },
      success: function success(result) {
        var sessionId = result.data.sessionId;
        stripe.redirectToCheckout({
          sessionId: sessionId
        }).then(function (result) {
          manageAjaxErrors(result);
        });
      },
      error: function error(result) {
        displayErrorMessage(result.responseJSON.message);
      },
      complete: function complete() {}
    });
  }
  if (paymentGatewayType == paytmMethod) {
    window.location.replace(route("paytm.init", {
      appointmentId: appointmentId
    }));
  }
  if (paymentGatewayType == authorizeMethod) {
    window.location.replace(route("authorize.init", {
      appointmentId: appointmentId
    }));
  }
  if (paymentGatewayType == paypalMethod) {
    $.ajax({
      type: "GET",
      url: route("paypal.init"),
      data: {
        appointmentId: appointmentId
      },
      success: function success(result) {
        if (result.status == 200) {
          var redirectTo = "";
          location.href = result.link;
          // $.each(result.result.links,
          //     function (key, val) {
          //         if (val.rel == 'approve') {
          //             redirectTo = val.href;
          //         }
          //     });
          // location.href = redirectTo;
        }
      },
      error: function error(result) {
        displayErrorMessage(result.responseJSON.message);
      },
      complete: function complete() {}
    });
  }
  return false;
});
listenClick(".payment-btn", function (event) {
  var appointmentId = $(this).attr("data-id");
  $("#paymentGatewayModal").modal("show").appendTo("body");
  $("#patientAppointmentId").val(appointmentId);
});
listen("hidden.bs.modal", "#paymentGatewayModal", function (e) {
  $("#patientPaymentForm")[0].reset();
  $("#paymentGatewayType").val(null).trigger("change");
});

/***/ }),

/***/ "./resources/assets/js/appointments/patient-calendar.js":
/*!**************************************************************!*\
  !*** ./resources/assets/js/appointments/patient-calendar.js ***!
  \**************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadPatientAppointmentCalendar);
var popover;
var popoverState = false;
var calendar;
var data = {
  id: '',
  uId: '',
  eventName: '',
  eventDescription: '',
  eventStatus: '',
  startDate: '',
  endDate: '',
  amount: 0,
  service: '',
  doctorName: ''
};

// View event variables
var viewEventName, viewEventDescription, viewEventStatus, viewStartDate, viewEndDate, viewModal, viewEditButton, viewDeleteButton, viewService, viewUId, viewAmount;
function loadPatientAppointmentCalendar() {
  if (!$('#appointmentCalendar').length) {
    return;
  }
  initCalendarApp();
  init();
}
var initCalendarApp = function initCalendarApp() {
  if (usersRole != 'patient') {
    return;
  }
  var lang = $('.currentLanguage').val();
  var calendarEl = document.getElementById('appointmentCalendar');
  calendar = new FullCalendar.Calendar(calendarEl, {
    locale: lang,
    themeSystem: 'bootstrap5',
    height: 750,
    buttonText: {
      today: Lang.get('js.datepicker.today'),
      day: Lang.get('js.day'),
      month: Lang.get('js.month')
    },
    headerToolbar: {
      left: 'title',
      center: 'prev,next today',
      right: 'dayGridDay,dayGridMonth'
    },
    initialDate: new Date(),
    timeZone: 'UTC',
    dayMaxEvents: true,
    events: function events(info, successCallback, failureCallback) {
      $.ajax({
        url: route('patients.appointments.calendar'),
        type: 'GET',
        data: info,
        success: function success(result) {
          if (result.success) {
            successCallback(result.data);
          }
        },
        error: function error(result) {
          displayErrorMessage(result.responseJSON.message);
          failureCallback();
        }
      });
    },
    // MouseEnter event --- more info: https://fullcalendar.io/docs/eventMouseEnter
    eventMouseEnter: function eventMouseEnter(arg) {
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        description: arg.event.extendedProps.description,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        doctorName: arg.event.extendedProps.doctorName
      });

      // Show popover preview
      initPopovers(arg.el);
    },
    eventMouseLeave: function eventMouseLeave() {
      hidePopovers();
    },
    // Click event --- more info: https://fullcalendar.io/docs/eventClick
    eventClick: function eventClick(arg) {
      hidePopovers();
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        description: arg.event.extendedProps.description,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        doctorName: arg.event.extendedProps.doctorName
      });
      handleViewEvent();
    }
  });
  calendar.render();
};
var init = function init() {
  if (!$('#patientEventModal').length) {
    return;
  }
  var viewElement = document.getElementById('patientEventModal');
  viewModal = new bootstrap.Modal(viewElement);
  viewEventName = viewElement.querySelector('[data-calendar="event_name"]');
  viewEventDescription = viewElement.querySelector('[data-calendar="event_description"]');
  viewEventStatus = viewElement.querySelector('[data-calendar="event_status"]');
  viewAmount = viewElement.querySelector('[data-calendar="event_amount"]');
  viewUId = viewElement.querySelector('[data-calendar="event_uId"]');
  viewService = viewElement.querySelector('[data-calendar="event_service"]');
  viewStartDate = viewElement.querySelector('[data-calendar="event_start_date"]');
  viewEndDate = viewElement.querySelector('[data-calendar="event_end_date"]');
};

// Format FullCalendar responses
var formatArgs = function formatArgs(res) {
  data.id = res.id;
  data.eventName = res.title;
  data.eventDescription = res.description;
  data.eventStatus = res.status;
  data.startDate = res.startStr;
  data.endDate = res.endStr;
  data.amount = res.amount;
  data.uId = res.uId;
  data.service = res.service;
  data.doctorName = res.doctorName;
};

// Initialize popovers --- more info: https://getbootstrap.com/docs/4.0/components/popovers/
var initPopovers = function initPopovers(element) {
  hidePopovers();

  // Generate popover content
  var startDate = data.allDay ? moment(data.startDate).format('Do MMM, YYYY') : moment(data.startDate).format('Do MMM, YYYY - h:mm a');
  var endDate = data.allDay ? moment(data.endDate).format('Do MMM, YYYY') : moment(data.endDate).format('Do MMM, YYYY - h:mm a');
  var popoverHtml = '<div class="fw-bolder mb-2"><b>Doctor</b>: ' + data.doctorName + '</div><div class="fs-7"><span class="fw-bold">Start:</span> ' + startDate + '</div><div class="fs-7 mb-4"><span class="fw-bold">End:</span> ' + endDate + '</div>';

  // Popover options
  var options = {
    container: 'body',
    trigger: 'manual',
    boundary: 'window',
    placement: 'auto',
    dismiss: true,
    html: true,
    title: 'Appointment Details',
    content: popoverHtml
  };
};

// Hide active popovers
var hidePopovers = function hidePopovers() {
  if (popoverState) {
    popover.dispose();
    popoverState = false;
  }
};

// Handle view event
var handleViewEvent = function handleViewEvent() {
  $('.fc-popover').addClass('hide');
  viewModal.show();

  // Detect all day event
  var eventNameMod;
  var startDateMod;
  var endDateMod;
  eventNameMod = '';
  startDateMod = moment(data.startDate).utc().format('Do MMM, YYYY - h:mm A');
  endDateMod = moment(data.endDate).utc().format('Do MMM, YYYY - h:mm A');
  viewEndDate.innerText = ': ' + endDateMod;
  viewStartDate.innerText = ': ' + startDateMod;

  // Populate view data
  viewEventName.innerText = 'Doctor: ' + data.doctorName;
  $(viewEventStatus).val(data.eventStatus);
  viewAmount.innerText = addCommas(data.amount);
  viewUId.innerText = data.uId;
  viewService.innerText = data.service;
};

/***/ }),

/***/ "./resources/assets/js/brands/brands.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/brands/brands.js ***!
  \**********************************************/
/***/ (() => {

"use strict";


listenClick('.brand-delete-btn', function (event) {
  var brandId = $(event.currentTarget).attr('data-id');
  deleteItem(route('brands.destroy', brandId), Lang.get('js.brand'));
});
listenSubmit('#createBrandForm, #editBrandForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    return false;
  }
});

/***/ }),

/***/ "./resources/assets/js/category/category.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/category/category.js ***!
  \**************************************************/
/***/ (() => {

"use strict";


Livewire.hook('element.init', function (_ref) {
  var component = _ref.component,
    el = _ref.el;
  if ($('#medicineCategoryHead').length) {
    $('#medicineCategoryHead').select2();
  }
});
listenClick('.add-category', function () {
  $('#add_categories_modal').modal('show').appendTo('body');
});
listenSubmit('#addMedicineCategoryForm', function (event) {
  event.preventDefault();
  var loadingButton = jQuery(this).find('#medicineCategorySave');
  loadingButton.button('loading');
  $.ajax({
    url: $('#indexCategoryCreateUrl').val(),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#add_categories_modal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      loadingButton.button('reset');
    }
  });
});
listenSubmit('#editMedicineCategoryForm', function (event) {
  event.preventDefault();
  var loadingButton = jQuery(this).find('#editCategorySave');
  loadingButton.button('loading');
  var id = $('#editMedicineCategoryId').val();
  $.ajax({
    url: route('categories.update', id),
    type: 'put',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#edit_categories_modal').modal('hide');
        if ($('#categoriesShowUrl').length) {
          window.location.href = $('#categoriesShowUrl').val();
        } else {
          Livewire.dispatch('refresh');
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      loadingButton.button('reset');
    }
  });
});
listen('hidden.bs.modal', '#add_categories_modal', function () {
  resetModalForm('#addMedicineCategoryForm', '#medicineCategoryErrorsBox');
});
listen('hidden.bs.modal', '#edit_categories_modal', function () {
  resetModalForm('#editMedicineCategoryForm', '#editMedicineCategoryErrorsBox');
});
function renderCategoryData(id) {
  $.ajax({
    url: route('categories.edit', id),
    type: 'GET',
    success: function success(result) {
      if (result.success) {
        var category = result.data;
        $('#editMedicineCategoryId').val(category.id);
        $('#editCategoryName').val(category.name);
        if (category.is_active === 1) $('#editCategoryIsActive').prop('checked', true);else $('#editCategoryIsActive').prop('checked', false);
        $('#edit_categories_modal').modal('show');
        ajaxCallCompleted();
      }
    },
    error: function error(result) {
      manageAjaxErrors(result);
    }
  });
}
listenClick('.category-edit-btn', function (event) {
  if ($('.ajaxCallIsRunning').val()) {
    return;
  }
  ajaxCallInProgress();
  var categoryId = $(event.currentTarget).attr('data-id');
  renderCategoryData(categoryId);
});
listenClick('.category-delete-btn', function (event) {
  var categoryId = $(event.currentTarget).attr('data-id');
  deleteItem(route('categories.destroy', categoryId), Lang.get('js.category'));
});

// category activation deactivation change event
listenChange('.medicine-category-status', function (event) {
  var categoryId = $(event.currentTarget).attr('data-id');
  activeDeActiveCategory(categoryId);
});
listenClick('#categoryResetFilter', function () {
  $('#medicineCategoryHead').val(0).trigger('change');
  hideDropdownManually($('#medicineCategoryFilterBtn'), $('.dropdown-menu'));
});

// activate de-activate category
function activeDeActiveCategory(id) {
  $.ajax({
    url: route('active.deactive', id),
    method: 'post',
    cache: false,
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        Livewire.dispatch('refresh');
      }
    }
  });
}
;
listenChange('#medicineCategoryHead', function () {
  Livewire.dispatch('changeFilter', {
    value: $(this).val()
  });
  hideDropdownManually($('#medicineCategoryFilterBtn'), $('#medicineCategoryFilter'));
});

/***/ }),

/***/ "./resources/assets/js/cities/cities.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/cities/cities.js ***!
  \**********************************************/
/***/ (() => {

listenClick("#createCity", function () {
  $("#createCityModal").modal("show").appendTo("body");
  $("#stateCity").select2({
    dropdownParent: $("#createCityModal")
  });
});
listen("hidden.bs.modal", "#createCityModal", function () {
  resetModalForm("#createCityForm", "#createCityValidationErrorsBox");
  $("#stateCity").val(null).trigger("change");
});
listen("hidden.bs.modal", "#editCityModal", function () {
  resetModalForm("#editCityForm", "#editCityValidationErrorsBox");
});
listenClick(".city-edit-btn", function (event) {
  var editCityId = $(event.currentTarget).attr("data-id");
  renderData(editCityId);
  $("#editCityStateId").select2({
    dropdownParent: $("#editCityModal")
  });
});
function renderData(id) {
  $.ajax({
    url: route("cities.edit", id),
    type: "GET",
    success: function success(result) {
      $("#cityID").val(result.data.id);
      $("#editCityName").val(result.data.name);
      $("#editCityStateId").val(result.data.state_id).trigger("change");
      $("#editCityModal").modal("show");
    }
  });
}
listenSubmit("#createCityForm", function (e) {
  e.preventDefault();
  $.ajax({
    url: route("cities.store"),
    type: "POST",
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $("#createCityModal").modal("hide");
        Livewire.dispatch("refresh");
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit("#editCityForm", function (e) {
  e.preventDefault();
  var updateCityId = $("#cityID").val();
  $.ajax({
    url: route("cities.update", updateCityId),
    type: "PUT",
    data: $(this).serialize(),
    success: function success(result) {
      $("#editCityModal").modal("hide");
      displaySuccessMessage(result.message);
      Livewire.dispatch("refresh");
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick(".city-delete-btn", function (event) {
  var cityRecordId = $(event.currentTarget).attr("data-id");
  deleteItem(route("cities.destroy", cityRecordId), Lang.get("js.city"));
});

/***/ }),

/***/ "./resources/assets/js/clinic_schedule/create-edit.js":
/*!************************************************************!*\
  !*** ./resources/assets/js/clinic_schedule/create-edit.js ***!
  \************************************************************/
/***/ (() => {

listenSubmit('#clinicScheduleSaveForm', function (e) {
  e.preventDefault();
  var data = new FormData($(this)[0]);
  $.ajax({
    url: route('checkRecord'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      saveUpdateForm(data);
    },
    error: function error(result) {
      swal({
        title: Lang.get('js.deleted'),
        text: result.responseJSON.message,
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        closeOnConfirm: true,
        confirmButtonColor: '#266CB0',
        showLoaderOnConfirm: true,
        cancelButtonText: Lang.get('js.no'),
        confirmButtonText: Lang.get('js.yes_update')
      }).then(function (result) {
        if (result) {
          saveUpdateForm(data);
        }
      });
    }
  });
});
function saveUpdateForm(data) {
  $.ajax({
    url: route('clinic-schedules.store'),
    type: 'POST',
    data: data,
    processData: false,
    contentType: false,
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        setTimeout(function () {
          location.reload();
        }, 1500);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {}
  });
}
listenChange('select[name^="clinicStartTimes"]', function (e) {
  var selectedIndex = $(this)[0].selectedIndex;
  var endTimeOptions = $(this).closest('.weekly-row').find('select[name^="clinicEndTimes"] option');
  endTimeOptions.eq(selectedIndex + 1).prop('selected', true).trigger('change');
  endTimeOptions.each(function (index) {
    if (index <= selectedIndex) {
      $(this).attr('disabled', true);
    } else {
      $(this).attr('disabled', false);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/countries/countries.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/countries/countries.js ***!
  \****************************************************/
/***/ (() => {

listenClick('.country-delete-btn', function (event) {
  var countryRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('countries.destroy', countryRecordId), Lang.get('js.country'));
});
listenClick('#addCountry', function () {
  $('#addCountryModal').modal('show').appendTo('body');
});
listenSubmit('#addCountryForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('countries.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#addCountryModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('.country-edit-btn', function (event) {
  $('#editCountryModal').modal('show').appendTo('body');
  var editCountryId = $(event.currentTarget).attr('data-id');
  $('#editCountryId').val(editCountryId);
  $.ajax({
    url: route('countries.edit', editCountryId),
    type: 'GET',
    success: function success(result) {
      if (result.success) {
        $('#editCountryName').val(result.data.name);
        $('#editShortCodeName').val(result.data.short_code);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit('#editCountryForm', function (event) {
  event.preventDefault();
  var updateCountryId = $('#editCountryId').val();
  $.ajax({
    url: route('countries.update', updateCountryId),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#editCountryModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listen('hidden.bs.modal', '#addCountryModal', function (e) {
  $('#addCountryForm')[0].reset();
});

/***/ }),

/***/ "./resources/assets/js/currencies/currencies.js":
/*!******************************************************!*\
  !*** ./resources/assets/js/currencies/currencies.js ***!
  \******************************************************/
/***/ (() => {

listenClick('#createCurrency', function () {
  $('#createCurrencyModal').modal('show').appendTo('body');
});
listen('hidden.bs.modal', '#createCurrencyModal', function () {
  resetModalForm('#createCurrencyForm', '#createCurrencyValidationErrorsBox');
});
listen('hidden.bs.modal', '#editCurrencyModal', function () {
  resetModalForm('#editCurrencyForm', '#editCurrencyValidationErrorsBox');
});
listenClick('.currency-edit-btn', function (event) {
  var editCurrencyId = $(event.currentTarget).attr('data-id');
  renderData(editCurrencyId);
});
function renderData(id) {
  $.ajax({
    url: route('currencies.edit', id),
    type: 'GET',
    success: function success(result) {
      $('#currencyID').val(result.data.id);
      $('#editCurrency_Name').val(result.data.currency_name);
      $('#editCurrency_Icon').val(result.data.currency_icon);
      $('#editCurrency_Code').val(result.data.currency_code);
      $('#editCurrencyModal').modal('show');
    }
  });
}
listenSubmit('#createCurrencyForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('currencies.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#createCurrencyModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit('#editCurrencyForm', function (e) {
  e.preventDefault();
  var updateCurrencyId = $('#currencyID').val();
  $.ajax({
    url: route('currencies.update', updateCurrencyId),
    type: 'PUT',
    data: $(this).serialize(),
    success: function success(result) {
      $('#editCurrencyModal').modal('hide');
      displaySuccessMessage(result.message);
      Livewire.dispatch('refresh');
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {}
  });
});
listenClick('.currency-delete-btn', function (event) {
  var currencyRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('currencies.destroy', currencyRecordId), Lang.get('js.currency'));
});

/***/ }),

/***/ "./resources/assets/js/custom/create-account.js":
/*!******************************************************!*\
  !*** ./resources/assets/js/custom/create-account.js ***!
  \******************************************************/
/***/ (function() {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
document.addEventListener('DOMContentLoaded', loadAccountData);
function loadAccountData() {
  if (!$('#expiryMonth').length || !$('#expiryYear').length) {
    return;
  }
  $('#expiryMonth').select2();
  $('#expiryYear').select2();
}
listenClick('#submitBtn', function (event) {
  var valid = true;
  $('.demoInputBox').css('background-color', '');
  var message = '';
  var cardHolderNameRegex = /^[a-z ,.'-]+$/i;
  var cvvRegex = /^[0-9]{3,3}$/;
  var cardHolderName = $('#cardHolderName').val();
  var cardNumber = $('#cardNumber').val();
  var exMonth = $('#expiryMonth').val();
  var exYear = $('#expiryYear').val();
  var cvv = $('#cvv').val();
  if (cardHolderName == '') {
    message += 'Card holder name fields are required.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (cardHolderName != '' && !cardHolderNameRegex.test(cardHolderName)) {
    message = 'Card holder name is Invalid.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (cardNumber == '') {
    message = 'Card number fields are required.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (exMonth === '') {
    message = 'Expiration month fields are required.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (exYear === '') {
    message += 'Expiration year fields are required.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  exMonth = parseInt(exMonth) + 1;
  var expiryDate = new Date(exYear + '-' + exMonth + '-01');
  if (expiryDate < new Date()) {
    message += 'Enter valid expiration date.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (cvv == '') {
    message += 'CVV number fields are required.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (cvv != '' && !cvvRegex.test(cvv)) {
    message += 'CVV is invalid.';
    $('.error').html(message);
    $('#errorCard').addClass('show');
    return false;
  }
  if (cardNumber != '') {
    $('#cardNumber').validateCreditCard(function (result) {
      if (!result.valid) {
        message = 'Card number is invalid.';
        $('.error').html(message);
        $('#errorCard').addClass('show');
        return false;
      }
    });
  }
  if (message != '') {
    return false;
  }
});

// Generated by CoffeeScript 1.10.0
(function () {
  var $,
    Range,
    Trie,
    indexOf = [].indexOf || function (item) {
      for (var i = 0, l = this.length; i < l; i++) {
        if (i in this && this[i] === item) return i;
      }
      return -1;
    };
  Trie = function () {
    function Trie() {
      this.trie = {};
    }
    Trie.prototype.push = function (value) {
      var _char, i, j, len, obj, ref, results;
      value = value.toString();
      obj = this.trie;
      ref = value.split('');
      results = [];
      for (i = j = 0, len = ref.length; j < len; i = ++j) {
        _char = ref[i];
        if (obj[_char] == null) {
          if (i === value.length - 1) {
            obj[_char] = null;
          } else {
            obj[_char] = {};
          }
        }
        results.push(obj = obj[_char]);
      }
      return results;
    };
    Trie.prototype.find = function (value) {
      var _char2, i, j, len, obj, ref;
      value = value.toString();
      obj = this.trie;
      ref = value.split('');
      for (i = j = 0, len = ref.length; j < len; i = ++j) {
        _char2 = ref[i];
        if (obj.hasOwnProperty(_char2)) {
          if (obj[_char2] === null) {
            return true;
          }
        } else {
          return false;
        }
        obj = obj[_char2];
      }
    };
    return Trie;
  }();
  Range = function () {
    function Range(trie1) {
      this.trie = trie1;
      if (this.trie.constructor !== Trie) {
        throw Error('Range constructor requires a Trie parameter');
      }
    }
    Range.rangeWithString = function (ranges) {
      var j, k, len, n, r, range, ref, ref1, trie;
      if (typeof ranges !== 'string') {
        throw Error('rangeWithString requires a string parameter');
      }
      ranges = ranges.replace(/ /g, '');
      ranges = ranges.split(',');
      trie = new Trie();
      for (j = 0, len = ranges.length; j < len; j++) {
        range = ranges[j];
        if (r = range.match(/^(\d+)-(\d+)$/)) {
          for (n = k = ref = r[1], ref1 = r[2]; ref <= ref1 ? k <= ref1 : k >= ref1; n = ref <= ref1 ? ++k : --k) {
            trie.push(n);
          }
        } else if (range.match(/^\d+$/)) {
          trie.push(range);
        } else {
          throw Error('Invalid range \'' + r + '\'');
        }
      }
      return new Range(trie);
    };
    Range.prototype.match = function (number) {
      return this.trie.find(number);
    };
    return Range;
  }();
  $ = jQuery;
  $.fn.validateCreditCard = function (callback, options) {
    var bind, card, card_type, card_types, get_card_type, is_valid_length, is_valid_luhn, j, len, normalize, ref, validate, validate_number;
    card_types = [{
      name: 'amex',
      range: '34,37',
      valid_length: [15]
    }, {
      name: 'diners_club_carte_blanche',
      range: '300-305',
      valid_length: [14]
    }, {
      name: 'diners_club_international',
      range: '36',
      valid_length: [14]
    }, {
      name: 'jcb',
      range: '3528-3589',
      valid_length: [16]
    }, {
      name: 'laser',
      range: '6304, 6706, 6709, 6771',
      valid_length: [16, 17, 18, 19]
    }, {
      name: 'visa_electron',
      range: '4026, 417500, 4508, 4844, 4913, 4917',
      valid_length: [16]
    }, {
      name: 'visa',
      range: '4',
      valid_length: [13, 14, 15, 16, 17, 18, 19]
    }, {
      name: 'mastercard',
      range: '51-55,2221-2720',
      valid_length: [16]
    }, {
      name: 'discover',
      range: '6011, 622126-622925, 644-649, 65',
      valid_length: [16]
    }, {
      name: 'dankort',
      range: '5019',
      valid_length: [16]
    }, {
      name: 'maestro',
      range: '50, 56-69',
      valid_length: [12, 13, 14, 15, 16, 17, 18, 19]
    }, {
      name: 'uatp',
      range: '1',
      valid_length: [15]
    }];
    bind = false;
    if (callback) {
      if (_typeof(callback) === 'object') {
        options = callback;
        bind = false;
        callback = null;
      } else if (typeof callback === 'function') {
        bind = true;
      }
    }
    if (options == null) {
      options = {};
    }
    if (options.accept == null) {
      options.accept = function () {
        var j, len, results;
        results = [];
        for (j = 0, len = card_types.length; j < len; j++) {
          card = card_types[j];
          results.push(card.name);
        }
        return results;
      }();
    }
    ref = options.accept;
    for (j = 0, len = ref.length; j < len; j++) {
      card_type = ref[j];
      if (indexOf.call(function () {
        var k, len1, results;
        results = [];
        for (k = 0, len1 = card_types.length; k < len1; k++) {
          card = card_types[k];
          results.push(card.name);
        }
        return results;
      }(), card_type) < 0) {
        throw Error('Credit card type \'' + card_type + '\' is not supported');
      }
    }
    get_card_type = function get_card_type(number) {
      var k, len1, r, ref1;
      ref1 = function () {
        var l, len1, ref1, results;
        results = [];
        for (l = 0, len1 = card_types.length; l < len1; l++) {
          card = card_types[l];
          if (ref1 = card.name, indexOf.call(options.accept, ref1) >= 0) {
            results.push(card);
          }
        }
        return results;
      }();
      for (k = 0, len1 = ref1.length; k < len1; k++) {
        card_type = ref1[k];
        r = Range.rangeWithString(card_type.range);
        if (r.match(number)) {
          return card_type;
        }
      }
      return null;
    };
    is_valid_luhn = function is_valid_luhn(number) {
      var digit, k, len1, n, ref1, sum;
      sum = 0;
      ref1 = number.split('').reverse();
      for (n = k = 0, len1 = ref1.length; k < len1; n = ++k) {
        digit = ref1[n];
        digit = +digit;
        if (n % 2) {
          digit *= 2;
          if (digit < 10) {
            sum += digit;
          } else {
            sum += digit - 9;
          }
        } else {
          sum += digit;
        }
      }
      return sum % 10 === 0;
    };
    is_valid_length = function is_valid_length(number, card_type) {
      var ref1;
      return ref1 = number.length, indexOf.call(card_type.valid_length, ref1) >= 0;
    };
    validate_number = function validate_number(number) {
      var length_valid, luhn_valid;
      card_type = get_card_type(number);
      luhn_valid = false;
      length_valid = false;
      if (card_type != null) {
        luhn_valid = is_valid_luhn(number);
        length_valid = is_valid_length(number, card_type);
      }
      return {
        card_type: card_type,
        valid: luhn_valid && length_valid,
        luhn_valid: luhn_valid,
        length_valid: length_valid
      };
    };
    validate = function (_this) {
      return function () {
        var number;
        number = normalize($(_this).val());
        return validate_number(number);
      };
    }(this);
    normalize = function normalize(number) {
      return number.replace(/[ -]/g, '');
    };
    if (!bind) {
      return validate();
    }
    this.on('input.jccv', function (_this) {
      return function () {
        $(_this).off('keyup.jccv');
        return callback.call(_this, validate());
      };
    }(this));
    this.on('keyup.jccv', function (_this) {
      return function () {
        return callback.call(_this, validate());
      };
    }(this));
    callback.call(this, validate());
    return this;
  };
}).call(this);

/***/ }),

/***/ "./resources/assets/js/custom/custom.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/custom/custom.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

document.addEventListener('DOMContentLoaded', loadCustomData);
var source = null;
var jsrender = __webpack_require__(/*! jsrender */ "./node_modules/jsrender/jsrender.js");
var csrfToken = $('meta[name="csrf-token"]').attr('content');
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': csrfToken
  }
});
document.addEventListener('DOMContentLoaded', initAllComponents);
function initAllComponents() {
  select2initialize();
  refreshCsrfToken();
  alertInitialize();
  modalInputFocus();
  inputFocus();
  IOInitImageComponent();
  IOInitSidebar();
  tooltip();
  togglePassword();
  setLoginUserLanguage();
}
function tooltip() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}
function alertInitialize() {
  $('.alert').delay(5000).slideUp(300);
}
function refreshCsrfToken() {
  csrfToken = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken
    }
  });
}
function select2initialize() {
  $('[data-control="select2"]').each(function () {
    $(this).select2();
  });
}
document.addEventListener('click', function (e) {
  var filterBtnEle = $(e.target).closest('.show[data-ic-dropdown-btn="true"]');
  var filterDropDownEle = $(e.target).closest('.show[data-ic-dropdown="true"]');
  if (!(filterBtnEle.length > 0 || filterDropDownEle.length > 0)) {
    $('[data-ic-dropdown-btn="true"]').removeClass('show');
    $('[data-ic-dropdown="true"]').removeClass('show');
  }
});
document.addEventListener('livewire:load', function () {
  window.livewire.hook('message.processed', function () {
    $('[data-control="select2"]').each(function () {
      $(this).select2();
    });
  });
});
var inputFocus = function inputFocus() {
  $('input:text:not([readonly="readonly"]):not([name="search"]):not(.front-input)').first().focus();
};
var modalInputFocus = function modalInputFocus() {
  $(function () {
    $('.modal').on('shown.bs.modal', function () {
      if ($(this).find('input:text')[0]) {
        $(this).find('input:text')[0].focus();
      }
    });
  });
};
function loadCustomData() {
  // script to active parent menu if sub menu has currently active
  var hasActiveMenu = $(document).find('.nav-item.dropdown ul li').hasClass('active');
  if (hasActiveMenu) {
    $(document).find('.nav-item.dropdown ul li.active').parent('ul').css('display', 'block');
    $(document).find('.nav-item.dropdown ul li.active').parent('ul').parent('li').addClass('active');
  }
  if ($(window).width() > 992) {
    $('.no-hover').on('click', function () {
      $(this).toggleClass('open');
    });
  }
}
$(document).ajaxComplete(function () {
  // Required for Bootstrap tooltips in DataTables
  $('[data-toggle="tooltip"]').tooltip({
    'html': true,
    'offset': 10
  });
});
listen('select2:open', function () {
  var allFound = document.querySelectorAll('.select2-container--open .select2-search__field');
  allFound[allFound.length - 1].focus();
});
listen('focus', '.select2.select2-container', function (e) {
  var isOriginalEvent = e.originalEvent; // don't re-open on closing focus event
  var isSingleSelect = $(this).find('.select2-selection--single').length > 0; // multi-select will pass focus to input

  if (isOriginalEvent && isSingleSelect) {
    if ($('select').data('select2')) {
      $(this).siblings('select:enabled').select2('open');
    }
  }
});
$(function () {
  $('.modal').on('shown.bs.modal', function () {
    if ($(this).attr('class') != 'modal fade event-modal show') {
      $(this).find('input:text,input:password').first().focus();
    }
  });
});
toastr.options = {
  'closeButton': true,
  'debug': false,
  'newestOnTop': false,
  'progressBar': true,
  'positionClass': 'toast-top-right',
  'preventDuplicates': false,
  'onclick': null,
  'showDuration': '300',
  'hideDuration': '1000',
  'timeOut': '5000',
  'extendedTimeOut': '1000',
  'showEasing': 'swing',
  'hideEasing': 'linear',
  'showMethod': 'fadeIn',
  'hideMethod': 'fadeOut'
};
window.resetModalForm = function (formId, validationBox) {
  $(formId)[0].reset();
  $('select.select2Selector').each(function (index, element) {
    var drpSelector = '#' + $(this).attr('id');
    $(drpSelector).val('');
    $(drpSelector).trigger('change');
  });
  $(validationBox).hide();
};
window.printErrorMessage = function (selector, errorResult) {
  $(selector).show().html('');
  $(selector).text(errorResult.responseJSON.message);
};
window.manageAjaxErrors = function (data) {
  var errorDivId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'editValidationErrorsBox';
  if (data.status == 404) {
    toastr.error(data.responseJSON.message);
  } else if (data.status == 422) {
    toastr.error(data.responseJSON.message);
  } else {
    printErrorMessage('#' + errorDivId, data);
  }
};
window.displaySuccessMessage = function (message) {
  toastr.success(message);
};
window.displayErrorMessage = function (message) {
  toastr.error(message);
};
window.deleteItem = function (url, header) {
  var callFunction = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  swal({
    title: Lang.get('js.delete') + ' !',
    text: Lang.get('js.are_you_sure') + ' "' + header + '" ?',
    buttons: {
      confirm: Lang.get('js.yes'),
      cancel: Lang.get('js.no')
    },
    reverseButtons: true,
    icon: 'warning'
  }).then(function (willDelete) {
    if (willDelete) {
      deleteItemAjax(url, header, callFunction);
    }
  });
};
function deleteItemAjax(url, header) {
  var callFunction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  $.ajax({
    url: url,
    type: 'DELETE',
    dataType: 'json',
    success: function success(obj) {
      if (obj.success) {
        Livewire.dispatch('refresh');
        Livewire.dispatch('resetPage');
      }
      swal({
        icon: 'success',
        title: Lang.get('js.deleted'),
        text: header + ' ' + Lang.get('js.has_been'),
        timer: 2000,
        buttons: {
          confirm: Lang.get('js.ok')
        }
      });
      if (callFunction) {
        eval(callFunction);
      }
    },
    error: function error(data) {
      swal({
        title: Lang.get('js.error'),
        icon: 'error',
        text: data.responseJSON.message,
        type: 'error',
        timer: 4000,
        buttons: {
          confirm: Lang.get('js.ok')
        }
      });
    }
  });
}
window.format = function (dateTime) {
  var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'DD-MMM-YYYY';
  return moment(dateTime).format(format);
};
window.processingBtn = function (selecter, btnId) {
  var state = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  var loadingButton = $(selecter).find(btnId);
  if (state === 'loading') {
    loadingButton.button('loading');
  } else {
    loadingButton.button('reset');
  }
};
window.prepareTemplateRender = function (templateSelector, data) {
  var template = jsrender.templates(templateSelector);
  return template.render(data);
};
window.isValidFile = function (inputSelector, validationMessageSelector) {
  var ext = $(inputSelector).val().split('.').pop().toLowerCase();
  if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
    $(inputSelector).val('');
    $(validationMessageSelector).removeClass('d-none');
    $(validationMessageSelector).html(Lang.get("js.image_file_type")).show();
    $(validationMessageSelector).delay(5000).slideUp(300);
    return false;
  }
  $(validationMessageSelector).hide();
  return true;
};
window.displayPhoto = function (input, selector) {
  var displayPreview = true;
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      var image = new Image();
      image.src = e.target.result;
      image.onload = function () {
        $(selector).attr('src', e.target.result);
        displayPreview = true;
      };
    };
    if (input.files[0].size > 2097152) {
      displayErrorMessage(Lang.get("js.image_file_type"));
      return false;
    }
    if (displayPreview) {
      reader.readAsDataURL(input.files[0]);
      $(selector).show();
    }
  }
};
window.removeCommas = function (str) {
  return str.replace(/,/g, '');
};
window.DatetimepickerDefaults = function (opts) {
  return $.extend({}, {
    sideBySide: true,
    ignoreReadonly: true,
    icons: {
      close: 'fa fa-times',
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fa fa-arrow-up',
      down: 'fa fa-arrow-down',
      previous: 'fa fa-chevron-left',
      next: 'fa fa-chevron-right',
      today: 'fa fa-clock-o',
      clear: 'fa fa-trash-o'
    }
  }, opts);
};
window.isEmpty = function (value) {
  return value === undefined || value === null || value === '';
};
window.screenLock = function () {
  $('#overlay-screen-lock').show();
  $('body').css({
    'pointer-events': 'none',
    'opacity': '0.6'
  });
};
window.screenUnLock = function () {
  $('body').css({
    'pointer-events': 'auto',
    'opacity': '1'
  });
  $('#overlay-screen-lock').hide();
};
window.onload = function () {
  window.startLoader = function () {
    $('.infy-loader').show();
  };
  window.stopLoader = function () {
    $('.infy-loader').hide();
  };

  // infy loader js
  stopLoader();
};
window.setBtnLoader = function (btnLoader) {
  if (btnLoader.attr('data-old-text')) {
    btnLoader.html(btnLoader.attr('data-old-text')).prop('disabled', false);
    btnLoader.removeAttr('data-old-text');
    return;
  }
  btnLoader.attr('data-old-text', btnLoader.text());
  btnLoader.html('<i class="icon-line-loader icon-spin m-0"></i>').prop('disabled', true);
};
window.setAdminBtnLoader = function (btnLoader) {
  if (btnLoader.attr('data-old-text')) {
    btnLoader.html(btnLoader.attr('data-old-text')).prop('disabled', false);
    btnLoader.removeAttr('data-old-text');
    return;
  }
  btnLoader.attr('data-old-text', btnLoader.text());
  btnLoader.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
};
window.urlValidation = function (value, regex) {
  var urlCheck = value == '' ? true : value.match(regex) ? true : false;
  if (!urlCheck) {
    return false;
  }
  return true;
};
listenClick('.languageSelection', function () {
  var languageName = $(this).data('prefix-value');
  $.ajax({
    type: 'POST',
    url: '/change-language',
    data: {
      languageName: languageName
    },
    success: function success() {
      location.reload();
    }
  });
});
listenClick('#register', function (e) {
  e.preventDefault();
  $('.open #dropdownLanguage').trigger('click');
  $('.open #dropdownLogin').trigger('click');
});
listenClick('#language', function (e) {
  e.preventDefault();
  $('.open #dropdownRegister').trigger('click');
  $('.open #dropdownLogin').trigger('click');
});
listenClick('#login', function (e) {
  e.preventDefault();
  $('.open #dropdownRegister').trigger('click');
  $('.open #dropdownLanguage').trigger('click');
});
window.checkSummerNoteEmpty = function (selectorElement, errorMessage) {
  var isRequired = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
  if ($(selectorElement).summernote('isEmpty') && isRequired === 1) {
    displayErrorMessage(errorMessage);
    $(document).find('.note-editable').html('<p><br></p>');
    return false;
  } else if (!$(selectorElement).summernote('isEmpty')) {
    $(document).find('.note-editable').contents().each(function () {
      if (this.nodeType === 3) {
        // text node
        this.textContent = this.textContent.replace(/\u00A0/g, '');
      }
    });
    if ($(document).find('.note-editable').text().trim().length == 0) {
      $(document).find('.note-editable').html('<p><br></p>');
      $(selectorElement).val(null);
      if (isRequired === 1) {
        displayErrorMessage(errorMessage);
        return false;
      }
    }
  }
  return true;
};
window.preparedTemplate = function () {
  source = $('#actionTemplate').html();
  window.preparedTemplate = Handlebars.compile(source);
};
window.ajaxCallInProgress = function () {
  ajaxCallIsRunning = true;
};
window.ajaxCallCompleted = function () {
  ajaxCallIsRunning = false;
};
window.avoidSpace = function (event) {
  var k = event ? event.which : window.event.keyCode;
  if (k == 32) {
    return false;
  }
};
listenClick('#readNotification', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var notificationId = $(this).attr('data-id');
  var notification = $(this);
  $.ajax({
    type: 'POST',
    url: route('notifications.read', notificationId),
    data: {
      notificationId: notificationId
    },
    success: function success() {
      var count = parseInt($('#header-notification-counter').text());
      $('#header-notification-counter').text(count - 1);
      notification.remove();
      var notificationCounter = document.getElementsByClassName('readNotification').length;
      $('#counter').text(notificationCounter);
      if (notificationCounter == 0) {
        $('.notification-counter').addClass('d-none');
        $('#readAllNotification').addClass('d-none');
        $('.empty-state').removeClass('d-none');
        $('.notification-toggle').removeClass('beep');
      }
      displaySuccessMessage(Lang.get('js.notification_read'));
    },
    error: function error(_error) {
      manageAjaxErrors(_error);
    }
  });
});
listenClick('#readAllNotification', function (e) {
  e.preventDefault();
  e.stopPropagation();
  $.ajax({
    type: 'POST',
    url: route('notifications.read.all'),
    success: function success() {
      $('#header-notification-counter').text(0);
      $('#header-notification-counter').addClass('d-none');
      $('.readNotification').remove();
      $('#readAllNotification').addClass('d-none');
      $('.empty-state').removeClass('d-none');
      $('.notification-toggle').removeClass('beep');
      displaySuccessMessage(Lang.get('js.notification_read'));
    },
    error: function error(_error2) {
      manageAjaxErrors(_error2);
    }
  });
});
window.getAvgReviewHtmlData = function (reviews) {
  var ratingCount = reviews.length;
  var totalSumRating = 0;
  $(reviews).each(function (index, value) {
    totalSumRating += value.rating;
  });
  var avgRating = totalSumRating / ratingCount;
  var data = '<div class="avg-review-star-div d-flex align-self-center mb-1">';
  for (var i = 0; i < 5; i++) {
    if (avgRating > 0) {
      if (avgRating > 0.5) {
        data += '<i class="fas fa-star review-star"></i>';
      } else {
        data += '<i class="fas fa-star-half-alt review-star"></i>';
      }
    } else {
      data += '<i class="far fa-star review-star"></i>';
    }
    avgRating--;
  }
  data += '</div>';
  return data;
};
listenClick('.apply-dark-mode', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('update-dark-mode'),
    type: 'get',
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        setTimeout(function () {
          location.reload();
        }, 500);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
window.openDropdownManually = function (dropdownBtnEle, dropdownEle) {
  if (!dropdownBtnEle.hasClass('show')) {
    dropdownBtnEle.addClass('show');
    dropdownEle.addClass('show');
  } else {
    dropdownBtnEle.removeClass('show');
    dropdownEle.removeClass('show');
  }
};
window.hideDropdownManually = function (dropdownBtnEle, dropdownEle) {
  dropdownBtnEle.removeClass('show');
  dropdownEle.removeClass('show');
};
function togglePassword() {
  $('[data-toggle="password"]').each(function () {
    var input = $(this);
    var eye_btn = $(this).parent().find('.input-icon');
    eye_btn.css('cursor', 'pointer').addClass('input-password-hide');
    eye_btn.on('click', function () {
      if (eye_btn.hasClass('input-password-hide')) {
        eye_btn.removeClass('input-password-hide').addClass('input-password-show');
        eye_btn.find('.bi').removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
        input.attr('type', 'text');
      } else {
        eye_btn.removeClass('input-password-show').addClass('input-password-hide');
        eye_btn.find('.bi').removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
        input.attr('type', 'password');
      }
    });
  });
}
document.addEventListener('turbo:before-cache', function () {
  var currentSelect2 = '.select2-hidden-accessible';
  $(currentSelect2).each(function () {
    $(this).select2('destroy');
  });
  $(currentSelect2).each(function () {
    $(this).select2();
  });
  $('.toast').addClass('d-none');
});
function setLoginUserLanguage() {
  var checkLanguageSession = $('.currentLanguage').val();
  Lang.setLocale(checkLanguageSession);
}

// set N/A if span tag is empty
window.setValueOfEmptySpan = function () {
  $('span.showSpan').each(function () {
    if (!$(this).text()) {
      $(this).text('N/A');
    }
  });
};

/***/ }),

/***/ "./resources/assets/js/custom/helper.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/custom/helper.js ***!
  \**********************************************/
/***/ (() => {

window.listen = function (event, selector, callback) {
  $(document).on(event, selector, callback);
};
window.listenClick = function (selector, callback) {
  $(document).on('click', selector, callback);
};
window.listenSubmit = function (selector, callback) {
  $(document).on('submit', selector, callback);
};
window.listenChange = function (selector, callback) {
  $(document).on('change', selector, callback);
};
window.listenKeyup = function (selector, callback) {
  $(document).on('keyup', selector, callback);
};
window.listenHiddenBsModal = function (selector, callback) {
  $(document).on('hidden.bs.modal', selector, callback);
};

/***/ }),

/***/ "./resources/assets/js/custom/input_price_format.js":
/*!**********************************************************!*\
  !*** ./resources/assets/js/custom/input_price_format.js ***!
  \**********************************************************/
/***/ (() => {

"use strict";


window.setPrice = function (selector, price) {
  if (price != '' || price > 0) {
    if (typeof price !== 'number') {
      price = price.replace(/,/g, '');
    }
    var formattedPrice = addCommas(price);
    $(selector).val(formattedPrice);
  }
};
window.addCommas = function (nStr) {
  nStr += '';
  var x = nStr.split('.');
  var x1 = x[0];
  var x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
};
window.getFormattedPrice = function (price) {
  if (price != '' || price > 0) {
    if (typeof price !== 'number') {
      price = price.replace(/,/g, '');
    }
    return addCommas(price);
  }
};
window.priceFormatSelector = function (selector) {
  var afterDecimal = 2;
  $(document).on('input keyup keydown keypress', selector, function (event) {
    var price = $(this).val();
    if (price === '') {
      $(this).val('');
    } else {
      if (/^[0-9]+(,[0-9]+)*$/.test(price)) {
        $(this).val(getFormattedPrice(price));
        return true;
      } else {
        this.value = this.value.replace(/(\..*)\./g, '$1').replace(new RegExp("(\\.[\\d]{" + afterDecimal + "}).", "g"), '$1');
      }
    }
  });
};
window.removeCommas = function (str) {
  return str.replace(/,/g, '');
};
priceFormatSelector('.price-input');

/***/ }),

/***/ "./resources/assets/js/custom/phone-number-country-code.js":
/*!*****************************************************************!*\
  !*** ./resources/assets/js/custom/phone-number-country-code.js ***!
  \*****************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadPhoneNumberCountryCodeData);
function loadPhoneNumberCountryCodeData() {
  loadPhoneNumberCountryCode();
  userCreateForm();
  userEditForm();
  vcardEditForm();
  createSetting();
}
function loadPhoneNumberCountryCode() {
  if (!$('#phoneNumber').length) {
    return false;
  }
  var input = document.querySelector('#phoneNumber'),
    errorMsg = document.querySelector('#error-msg'),
    validMsg = document.querySelector('#valid-msg');
  var errorMap = [Lang.get('js.invalid_number'), Lang.get('js.invalid_country_number'), Lang.get('js.too_short'), Lang.get('js.too_long'), Lang.get('js.invalid_number')];

  // initialise plugin
  var intl = window.intlTelInput(input, {
    initialCountry: defaultCountryCodeValue,
    separateDialCode: true,
    geoIpLookup: function geoIpLookup(success, failure) {
      $.get('https://ipinfo.io', function () {}, 'jsonp').always(function (resp) {
        var countryCode = resp && resp.country ? resp.country : '';
        success(countryCode);
      });
    },
    utilsScript: '../../public/assets/js/inttel/js/utils.min.js'
  });
  var reset = function reset() {
    input.classList.remove('error');
    errorMsg.innerHTML = '';
    errorMsg.classList.add('d-none');
    validMsg.classList.add('d-none');
  };
  input.addEventListener('blur', function () {
    reset();
    if (input.value.trim()) {
      if (intl.isValidNumber()) {
        validMsg.classList.remove('d-none');
      } else {
        input.classList.add('error');
        var errorCode = intl.getValidationError();
        errorMsg.innerHTML = errorMap[errorCode];
        errorMsg.classList.remove('d-none');
      }
    }
  });

  // on keyup / change flag: reset
  input.addEventListener('change', reset);
  input.addEventListener('keyup', reset);
  if (typeof phoneNo != 'undefined' && phoneNo !== '') {
    setTimeout(function () {
      $('#phoneNumber').trigger('change');
    }, 500);
  }
  $('#phoneNumber').on('blur keyup change countrychange', function () {
    if (typeof phoneNo != 'undefined' && phoneNo !== '') {
      intl.setNumber('+' + phoneNo);
      phoneNo = '';
    }
    var getCode = intl.selectedCountryData['dialCode'];
    $('#prefix_code').val(getCode);
  });
  var getCode = intl.selectedCountryData['dialCode'];
  $('#prefix_code').val(getCode);
  var getPhoneNumber = $('#phoneNumber').val();
  var removeDashPhoneNumber = getPhoneNumber.replaceAll('-', ' ');
  var removeSpacePhoneNumber = removeDashPhoneNumber.replace(/\s/g, '');
  $('#phoneNumber').val(removeSpacePhoneNumber);
  $('#phoneNumber').focus();
  $('#phoneNumber').trigger('blur');
}
$(document).on('click', '.iti__country', function () {
  var flagClass = $('.iti__selected-flag>.iti__flag').attr('class');
  flagClass = flagClass.split(/\s+/)[1];
  var dialCodeVal = $('.iti__selected-dial-code').text();
  window.localStorage.setItem('flagClassLocal', flagClass);
  window.localStorage.setItem('dialCodeValLocal', dialCodeVal);
});
function userCreateForm() {
  if (!$('#userCreateForm').length) {
    return false;
  }
  $('#userCreateForm').submit(function () {
    if ($('#error-msg').text() !== '') {
      $('#phoneNumber').focus();
      return false;
    }
  });
}
function vcardEditForm() {
  if (!$('#editForm').length) {
    return false;
  }
  $('#editForm').submit(function () {
    if ($('#error-msg').text() !== '') {
      $('#phoneNumber').focus();
      return false;
    }
  });
}
function createSetting() {
  if (!$('#createSetting').length) {
    return false;
  }
  $('#createSetting').submit(function () {
    if ($('#error-msg').text() !== '') {
      $('#phoneNumber').focus();
      return false;
    }
  });
}
function userEditForm() {
  if (!$('#userEditForm').length) {
    return false;
  }
  $('#userEditForm').submit(function () {
    if ($('#error-msg').text() !== '') {
      $('#phoneNumber').focus();
      return false;
    }
  });
}

/***/ }),

/***/ "./resources/assets/js/custom/sidebar_menu.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/custom/sidebar_menu.js ***!
  \****************************************************/
/***/ (() => {

listenKeyup('#menuSearch', function () {
  var value = $(this).val().toLowerCase();
  $('.nav-item').filter(function () {
    $('.no-record').addClass('d-none');
    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    checkEmpty();
  });
});
function checkEmpty() {
  if ($('.nav-item:visible').last().length == 0) {
    $('.no-record').removeClass('d-none');
  }
}
listenClick('.sidebar-aside-toggle', function () {
  if ($(this).hasClass('active') === true) {
    $('.sidebar-search-box').addClass('d-none');
  } else {
    $('.sidebar-search-box').removeClass('d-none');
  }
});

/***/ }),

/***/ "./resources/assets/js/dashboard/dashboard.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/dashboard/dashboard.js ***!
  \****************************************************/
/***/ (() => {

// document.addEventListener('DOMContentLoaded', loadDashboardData)
// document.addEventListener('DOMContentLoaded', loadPatientDashboardData)
// document.addEventListener('DOMContentLoaded', loadDoctorDashboardData)

var amount = [];
var month = [];
var totalAmount = 0;
var chartType = "area";
var adminDashboardAppointmentChart = null;
Livewire.hook("element.init", function (_ref) {
  var component = _ref.component,
    el = _ref.el;
  loadDashboardData();
  loadPatientDashboardData();
  loadDoctorDashboardData();
});
function loadPatientDashboardData() {
  if (!$("#patientChartData").length) {
    return;
  }
  var patientChartData = JSON.parse($("#patientChartData").val());
  var lang = $(".currentLanguage").val();
  var currentDate = new Date();
  var currentMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var currentValue = patientChartData[1][currentMonth];
  $(".patient-month-total-amount").text(currencyIcon + " " + currentValue);
  currentDate.setMonth(currentDate.getMonth() - 1); // Move one month back
  var previousMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var previousMonthValue = patientChartData[1][previousMonth];
  var performancedataforprogressbabr;
  if (previousMonthValue === 0) {
    performancedataforprogressbabr = 100;
  } else if (currentValue == 0 && previousMonthValue == 0) {
    performancedataforprogressbabr = 0;
  } else {
    performancedataforprogressbabr = (currentValue - previousMonthValue) / Math.abs(previousMonthValue) * 100;
  }
  if (performancedataforprogressbabr > 100) {
    $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + "<i class='fa fa-arrow-up' aria-hidden='true'></i>");
    $(".bord-earning-card-body-amont").css("color", "green");
  } else {
    if (performancedataforprogressbabr < 0) {
      $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-down'></i>");
      $(".dashbord-earning-card-body-amont").removeClass("text-success").addClass("text-danger");
    } else {
      $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-up' aria-hidden='true'></i>");
      $(".dashbord-earning-card-body-amont").css("color", "green");
    }
  }
  var remainingPercenctageForProgressbar = performancedataforprogressbabr + 100;
  if (remainingPercenctageForProgressbar > 100) {
    remainingPercenctageForProgressbar = 100;
  }
  $(function () {
    var setRadial = function setRadial(percent) {
      $(".patient-js-radial-mask").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".patient-js-radial-fill").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".js-radial-fill_fix").css("transform", "rotate(" + 3.6 * percent + "deg)");
      $(".patient-js-radial-percent").html(percent + "%");
    };
    setRadial(Math.abs(remainingPercenctageForProgressbar).toFixed(0));
  });
}
function loadDashboardData() {
  if (!$("#adminChartData").length) {
    return;
  }
  var adminChartData = JSON.parse($("#adminChartData").val());
  var lang = $(".currentLanguage").val();
  var currentDate = new Date();
  var currentMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var currentValue = adminChartData[currentMonth];
  $(".total-amount").text(currencyIcon + " " + currentValue);
  currentDate.setMonth(currentDate.getMonth() - 1); // Move one month back
  var previousMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var previousMonthValue = adminChartData[previousMonth];
  var performancedataforprogressbabr;
  if (previousMonthValue == 0 && currentValue != 0) {
    performancedataforprogressbabr = 100;
  } else if (currentValue == 0 && previousMonthValue == 0) {
    performancedataforprogressbabr = 0;
  } else {
    performancedataforprogressbabr = (currentValue - previousMonthValue) / Math.abs(previousMonthValue) * 100;
  }
  if (performancedataforprogressbabr > 100) {
    $(".admin-dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + "<i class='fa fa-arrow-up' aria-hidden='true'></i>");
    $(".admin-dashbord-earning-card-body-amont").css("color", "green");
  } else {
    if (performancedataforprogressbabr < 0) {
      $(".admin-dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-down'></i>");
      $(".admin-dashbord-earning-card-body-amont").removeClass("text-success").addClass("text-danger");
    } else {
      $(".admin-dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-up' aria-hidden='true'></i>");
      $(".admin-dashbord-earning-card-body-amont").css("color", "green");
    }
  }
  var remainingPercenctageForProgressbar = performancedataforprogressbabr + 100;
  if (remainingPercenctageForProgressbar > 100) {
    remainingPercenctageForProgressbar = 100;
  }
  $(function () {
    var setRadial = function setRadial(percent) {
      $(".js-radial-mask").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".js-radial-fill").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".js-radial-fill_fix").css("transform", "rotate(" + 3.6 * percent + "deg)");
      $(".js-radial-percent").html(percent + "%");
    };
    setRadial(Math.abs(remainingPercenctageForProgressbar).toFixed(0));
  });
  month = [];
  amount = [];
  totalAmount = 0;
  $.each(adminChartData, function (key, value) {
    month.push(key);
    amount.push(value);
    totalAmount += value;
  });
  $(".totalEarning").text(totalAmount);
}
function loadDoctorDashboardData() {
  if (!$("#doctorChartData").length) {
    return;
  }
  var doctorChartData = JSON.parse($("#doctorChartData").val());
  var lang = $(".currentLanguage").val();
  var currentDate = new Date();
  var currentMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var currentValue = doctorChartData[2][currentMonth];
  // $('.thismontappointment').text(currentValue);
  $(".doctor-month-total-amount").text(currencyIcon + " " + currentValue);
  currentDate.setMonth(currentDate.getMonth() - 1); // Move one month back
  var previousMonth = currentDate.toLocaleString(lang, {
    month: "short"
  });
  var previousMonthValue = doctorChartData[2][previousMonth];
  var performancedataforprogressbabr;
  if (previousMonthValue == 0 && currentValue != 0) {
    performancedataforprogressbabr = 100;
  } else if (currentValue == 0 && previousMonthValue == 0) {
    performancedataforprogressbabr = 0;
  } else {
    performancedataforprogressbabr = (currentValue - previousMonthValue) / Math.abs(previousMonthValue) * 100;
  }
  if (performancedataforprogressbabr > 100) {
    $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + "<i class='fa fa-arrow-up' aria-hidden='true'></i>");
    $(".bord-earning-card-body-amont").css("color", "green");
  } else {
    if (performancedataforprogressbabr < 0) {
      $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-down'></i>");
      $(".dashbord-earning-card-body-amont").removeClass("text-success").addClass("text-danger");
    } else {
      $(".dashbord-earning-card-body-amont").html(performancedataforprogressbabr.toFixed(2) + "%" + " <i class='fa fa-arrow-up' aria-hidden='true'></i>");
      $(".dashbord-earning-card-body-amont").css("color", "green");
    }
  }
  var remainingPercenctageForProgressbar = performancedataforprogressbabr + 100;
  if (remainingPercenctageForProgressbar > 100) {
    remainingPercenctageForProgressbar = 100;
  }
  $(function () {
    var setRadial = function setRadial(percent) {
      $(".doctor-js-radial-mask").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".doctor-js-radial-fill").css("transform", "rotate(" + 1.8 * percent + "deg)");
      $(".js-radial-fill_fix").css("transform", "rotate(" + 3.6 * percent + "deg)");
      $(".doctor-js-radial-percent").html(percent + "%");
    };
    setRadial(Math.abs(remainingPercenctageForProgressbar).toFixed(0));
  });
  month = [];
  amount = [];
  appointmentmonth = [];
  appointmentvalue = [];
  totalAmount = 0;
  totalAppointment = 0;
  $.each(doctorChartData[0], function (key, value) {
    month.push(key);
    amount.push(value);
    totalAmount += value;
  });
  $.each(doctorChartData[1], function (key, value) {
    appointmentmonth.push(key);
    appointmentvalue.push(value);
    totalAppointment += value;
  });
  prepareDoctorAppointmentReport();
}
function prepareDoctorAppointmentReport() {
  if (!$("#appointmentDoctorChartId").length) {
    return;
  }
  $("#appointmentDoctorChartId").remove();
  $(".appointmentDoctorChart").append('<div id="appointmentDoctorChartId" style="height: 350px" class="card-rounded-bottom"></div>');
  var id = document.getElementById("appointmentDoctorChartId"),
    borderColor = "--bs-gray-200";
  id && new ApexCharts(id, {
    series: [{
      name: "appointment",
      type: chartType,
      stacked: !0,
      data: appointmentvalue
    }],
    chart: {
      fontFamily: "inherit",
      stacked: !0,
      type: chartType,
      height: 350,
      toolbar: {
        show: !1
      },
      background: dashboardChartBGColor
    },
    plotOptions: {
      bar: {
        stacked: !0,
        horizontal: !1,
        borderRadius: 4,
        columnWidth: ["12%"]
      }
    },
    legend: {
      show: !1
    },
    dataLabels: {
      enabled: !1
    },
    stroke: {
      curve: "smooth",
      show: !0,
      width: 2,
      colors: ["transparent"]
    },
    xaxis: {
      categories: appointmentmonth,
      axisBorder: {
        show: !1
      },
      axisTicks: {
        show: !1
      },
      labels: {
        style: {
          colors: dashboardChartFontColor,
          fontSize: "12px"
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: dashboardChartFontColor,
          fontSize: "12px"
        }
      }
    },
    fill: {
      opacity: 1
    },
    states: {
      normal: {
        filter: {
          type: "none",
          value: 0
        }
      },
      hover: {
        filter: {
          type: "none",
          value: 0
        }
      },
      active: {
        allowMultipleDataPointsSelection: !1,
        filter: {
          type: "none",
          value: 0
        }
      }
    },
    tooltip: {
      style: {
        fontSize: "12px"
      },
      y: {
        formatter: function formatter(e) {
          return " " + e;
        }
      }
    },
    grid: {
      borderColor: borderColor,
      strokeDashArray: 4,
      yaxis: {
        lines: {
          show: !0
        }
      },
      padding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },
    theme: {
      mode: "dark",
      palette: "palette1",
      monochrome: {
        enabled: false,
        color: "#13151f",
        shadeTo: "dark",
        shadeIntensity: 0.0
      }
    }
  }).render();

  // totalAmount = 0;
}
listenClick("#monthData", function (e) {
  e.preventDefault();
  $.ajax({
    url: route("patientData.dashboard"),
    type: "GET",
    data: {
      month: "month"
    },
    success: function success(result) {
      if (result.success) {
        $("#monthlyReport").empty();
        $(document).find("#week").removeClass("show active");
        $(document).find("#day").removeClass("show active");
        $(document).find("#month").addClass("show active");
        if (result.data.patients.data != "") {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              image: value.profile,
              name: value.user.full_name,
              email: value.user.email,
              patientId: value.patient_unique_id,
              registered: moment.parseZone(value.user.created_at).format("Do MMM Y hh:mm A"),
              appointment_count: value.appointments_count,
              route: route("patients.show", value.id)
            }];
            $(document).find("#monthlyReport").append(prepareTemplateRender("#adminDashboardTemplate", data));
          });
        } else {
          $(document).find("#monthlyReport").append("<tr class=\"text-center\">\n                                                    <td colspan=\"5\" class=\"text-muted fw-bold\">".concat(noData, "</td>\n                                                </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenChange("#serviceId", function (e) {
  e.preventDefault();
  var serviceId = $("#serviceId").val();
  var dashboardDoctorId = $("#dashboardDoctorId").val();
  var serviceCategoryId = $("#serviceCategoryId").val();
  $(".totalEarning").text("");
  if ($(this).val() != "") {
    $.ajax({
      url: route("admin.dashboard"),
      type: "GET",
      data: {
        serviceId: serviceId,
        dashboardDoctorId: dashboardDoctorId,
        serviceCategoryId: serviceCategoryId
      },
      success: function success(result) {
        if (result.success) {
          month = [];
          amount = [];
          totalAmount = 0;
          $.each(result.data, function (key, value) {
            month.push(key);
            amount.push(value);
            totalAmount += value;
          });
          $(".totalEarning").text(totalAmount);
        }
      },
      error: function error(result) {
        displayErrorMessage(result.responseJSON.message);
      }
    });
  }
});
listenClick("#dashboardResetBtn", function () {
  $(".dashboardFilter").val("").trigger("change");
  hideDropdownManually($("#dashboardFilterBtn"), $(".dropdown-menu"));
});
listenChange("#dashboardDoctorId", function (e) {
  e.preventDefault();
  var serviceId = $("#serviceId").val();
  var dashboardDoctorId = $("#dashboardDoctorId").val();
  var serviceCategoryId = $("#serviceCategoryId").val();
  $(".totalEarning").text("");
  $.ajax({
    url: route("admin.dashboard"),
    type: "GET",
    data: {
      serviceId: serviceId,
      dashboardDoctorId: dashboardDoctorId,
      serviceCategoryId: serviceCategoryId
    },
    success: function success(result) {
      if (result.success) {
        month = [];
        amount = [];
        totalAmount = 0;
        $.each(result.data, function (key, value) {
          month.push(key);
          amount.push(value);
          totalAmount += value;
        });
        $(".totalEarning").text(totalAmount);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenChange("#serviceCategoryId", function (e) {
  e.preventDefault();
  var serviceId = $("#serviceId").val();
  var dashboardDoctorId = $("#dashboardDoctorId").val();
  var serviceCategoryId = $("#serviceCategoryId").val();
  $(".totalEarning").text("");
  $.ajax({
    url: route("admin.dashboard"),
    type: "GET",
    data: {
      serviceId: serviceId,
      dashboardDoctorId: dashboardDoctorId,
      serviceCategoryId: serviceCategoryId
    },
    success: function success(result) {
      if (result.success) {
        month = [];
        amount = [];
        totalAmount = 0;
        $.each(result.data, function (key, value) {
          month.push(key);
          amount.push(value);
          totalAmount += value;
        });
        $(".totalEarning").text(totalAmount);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick("#weekData", function (e) {
  e.preventDefault();
  $.ajax({
    url: route("patientData.dashboard"),
    type: "GET",
    data: {
      week: "week"
    },
    success: function success(result) {
      if (result.success) {
        $("#weeklyReport").empty();
        $(document).find("#month").removeClass("show active");
        $(document).find("#day").removeClass("show active");
        $(document).find("#week").addClass("show active");
        if (result.data.patients.data != "") {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              image: value.profile,
              name: value.user.full_name,
              email: value.user.email,
              patientId: value.patient_unique_id,
              registered: moment.parseZone(value.user.created_at).format("Do MMM Y hh:mm A"),
              appointment_count: value.appointments_count,
              route: route("patients.show", value.id)
            }];
            $(document).find("#weeklyReport").append(prepareTemplateRender("#adminDashboardTemplate", data));
          });
        } else {
          $(document).find("#weeklyReport").append("<tr class=\"text-center\">\n                                                    <td colspan=\"5\" class=\"text-muted fw-bold\">".concat(noData, "</td>\n                                                </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick("#dayData", function (e) {
  e.preventDefault();
  $.ajax({
    url: route("patientData.dashboard"),
    type: "GET",
    data: {
      day: "day"
    },
    success: function success(result) {
      if (result.success) {
        $("#dailyReport").empty();
        $(document).find("#month").removeClass("show active");
        $(document).find("#week").removeClass("show active");
        $(document).find("#day").addClass("show active");
        if (result.data.patients.data != "") {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              image: value.profile,
              name: value.user.full_name,
              email: value.user.email,
              patientId: value.patient_unique_id,
              registered: moment.parseZone(value.user.created_at).format("Do MMM Y hh:mm A"),
              appointment_count: value.appointments_count,
              route: route("patients.show", value.id)
            }];
            $(document).find("#dailyReport").append(prepareTemplateRender("#adminDashboardTemplate", data));
          });
        } else {
          $(document).find("#dailyReport").append("\n                    <tr class=\"text-center\">\n                        <td colspan=\"5\" class=\"text-muted fw-bold\"> ".concat(noData, "</td>\n                    </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick(".dayData", function () {
  $(this).addClass("text-primary");
  $(".weekData ,.monthData").removeClass("text-primary");
});
listenClick(".weekData", function () {
  $(this).addClass("text-primary");
  $(".dayData ,.monthData").removeClass("text-primary");
});
listenClick(".monthData", function () {
  $(this).addClass("text-primary");
  $(".weekData ,.dayData").removeClass("text-primary");
});

/***/ }),

/***/ "./resources/assets/js/dashboard/doctor-dashboard.js":
/*!***********************************************************!*\
  !*** ./resources/assets/js/dashboard/doctor-dashboard.js ***!
  \***********************************************************/
/***/ (() => {

listenClick('#doctorMonthData', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('doctors.appointment.dashboard'),
    type: 'GET',
    data: {
      month: 'month'
    },
    success: function success(result) {
      if (result.success) {
        $('#doctorMonthlyReport').empty();
        $(document).find('#week').removeClass('show active');
        $(document).find('#day').removeClass('show active');
        $(document).find('#month').addClass('show active');
        if (result.data.patients.data != '') {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              'image': value.patient.profile,
              'name': value.patient.user.full_name,
              'email': value.patient.user.email,
              'patientId': value.patient.patient_unique_id,
              'date': moment(value.date).format('Do MMM, Y'),
              'from_time': value.from_time,
              'from_time_type': value.from_time_type,
              'to_time': value.to_time,
              'to_time_type': value.to_time_type,
              'route': route('doctors.patient.detail', value.patient_id)
            }];
            $(document).find('#doctorMonthlyReport').append(prepareTemplateRender('#doctorDashboardTemplate', data));
          });
        } else {
          $(document).find('#doctorMonthlyReport').append("\n                                                <tr>\n                                                    <td colspan=\"4\" class=\"text-center fw-bold text-muted\">".concat(noData, "</td>\n                                                </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('#doctorWeekData', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('doctors.appointment.dashboard'),
    type: 'GET',
    data: {
      week: 'week'
    },
    success: function success(result) {
      if (result.success) {
        $('#doctorWeeklyReport').empty();
        $(document).find('#month').removeClass('show active');
        $(document).find('#day').removeClass('show active');
        $(document).find('#week').addClass('show active');
        if (result.data.patients.data != '') {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              'image': value.patient.profile,
              'name': value.patient.user.full_name,
              'email': value.patient.user.email,
              'patientId': value.patient.patient_unique_id,
              'date': moment(value.date).format('Do MMM, Y'),
              'from_time': value.from_time,
              'from_time_type': value.from_time_type,
              'to_time': value.to_time,
              'to_time_type': value.to_time_type,
              'route': route('doctors.patient.detail', value.patient_id)
            }];
            $(document).find('#doctorWeeklyReport').append(prepareTemplateRender('#doctorDashboardTemplate', data));
          });
        } else {
          $(document).find('#doctorWeeklyReport').append("\n                                                <tr>\n                                                    <td colspan=\"4\" class=\"text-center fw-bold text-muted\">".concat(noData, "</td>\n                                                </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('#doctorDayData', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('doctors.appointment.dashboard'),
    type: 'GET',
    data: {
      day: 'day'
    },
    success: function success(result) {
      if (result.success) {
        $('#doctorDailyReport').empty();
        $(document).find('#month').removeClass('show active');
        $(document).find('#week').removeClass('show active');
        $(document).find('#day').addClass('show active');
        if (result.data.patients.data != '') {
          $.each(result.data.patients.data, function (index, value) {
            var data = [{
              'image': value.patient.profile,
              'name': value.patient.user.full_name,
              'email': value.patient.user.email,
              'patientId': value.patient.patient_unique_id,
              'date': moment(value.date).format('Do MMM, Y'),
              'from_time': value.from_time,
              'from_time_type': value.from_time_type,
              'to_time': value.to_time,
              'to_time_type': value.to_time_type,
              'route': route('doctors.patient.detail', value.patient_id)
            }];
            $(document).find('#doctorDailyReport').append(prepareTemplateRender('#doctorDashboardTemplate', data));
          });
        } else {
          $(document).find('#doctorDailyReport').append("\n                                                <tr>\n                                                    <td colspan=\"4\" class=\"text-center fw-bold text-muted\">".concat(noData, "</td>\n                                                </tr>"));
        }
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('#doctorDayData', function () {
  $(this).addClass('text-primary');
  $('#doctorWeekData ,#doctorMonthData').removeClass('text-primary');
});
listenClick('#doctorWeekData', function () {
  $(this).addClass('text-primary');
  $('#doctorDayData ,#doctorMonthData').removeClass('text-primary');
});
listenClick('#doctorMonthData', function () {
  $(this).addClass('text-primary');
  $('#doctorWeekData ,#doctorDayData').removeClass('text-primary');
});

/***/ }),

/***/ "./resources/assets/js/doctor_appointments/calendar.js":
/*!*************************************************************!*\
  !*** ./resources/assets/js/doctor_appointments/calendar.js ***!
  \*************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadDoctorAppointmentCalendar);
var popover;
var popoverState = false;
var appointmentStatusId = null;
var doctorAppointmentCalendar;
var data = {
  id: '',
  uId: '',
  eventName: '',
  eventDescription: '',
  eventStatus: '',
  startDate: '',
  endDate: '',
  amount: 0,
  service: '',
  patientName: ''
};

// View event variables
var viewEventName, viewEventDescription, viewEventStatus, viewStartDate, viewEndDate, viewModal, viewService, viewUId, viewAmount;
function loadDoctorAppointmentCalendar() {
  initCalendarApp();
  init();
}
var initCalendarApp = function initCalendarApp() {
  if (usersRole != 'doctor') {
    return;
  }
  var calendarEl = document.getElementById('doctorAppointmentCalendar');
  if (!$(calendarEl).length) {
    return;
  }
  var lang = $('.currentLanguage').val();
  doctorAppointmentCalendar = new FullCalendar.Calendar(calendarEl, {
    locale: lang,
    themeSystem: 'bootstrap5',
    height: 750,
    buttonText: {
      today: Lang.get('js.today'),
      day: Lang.get('js.day'),
      month: Lang.get('js.month')
    },
    headerToolbar: {
      left: 'title',
      center: 'prev,next today',
      right: 'dayGridDay,dayGridMonth'
    },
    initialDate: new Date(),
    timeZone: 'UTC',
    dayMaxEvents: true,
    events: function events(info, successCallback, failureCallback) {
      $.ajax({
        url: route('doctors.appointments.calendar'),
        type: 'GET',
        data: info,
        success: function success(result) {
          if (result.success) {
            successCallback(result.data);
          }
        },
        error: function error(result) {
          displayErrorMessage(result.responseJSON.message);
          failureCallback();
        }
      });
    },
    // MouseEnter event --- more info: https://fullcalendar.io/docs/eventMouseEnter
    eventMouseEnter: function eventMouseEnter(arg) {
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        description: arg.event.extendedProps.description,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        patientName: arg.event.extendedProps.patientName
      });

      // Show popover preview
      initPopovers(arg.el);
    },
    eventMouseLeave: function eventMouseLeave() {
      hidePopovers();
    },
    // Click event --- more info: https://fullcalendar.io/docs/eventClick
    eventClick: function eventClick(arg) {
      hidePopovers();
      appointmentStatusId = arg.event.id;
      formatArgs({
        id: arg.event.id,
        title: arg.event.title,
        startStr: arg.event.startStr,
        endStr: arg.event.endStr,
        description: arg.event.extendedProps.description,
        status: arg.event.extendedProps.status,
        amount: arg.event.extendedProps.amount,
        uId: arg.event.extendedProps.uId,
        service: arg.event.extendedProps.service,
        patientName: arg.event.extendedProps.patientName
      });
      handleViewEvent();
    }
  });
  doctorAppointmentCalendar.render();
};
var init = function init() {
  if (!$('#doctorAppointmentCalendarModal').length) {
    return;
  }
  var viewElement = document.getElementById('doctorAppointmentCalendarModal');
  viewModal = new bootstrap.Modal(viewElement);
  viewEventName = viewElement.querySelector('[data-calendar="event_name"]');
  viewEventDescription = viewElement.querySelector('[data-calendar="event_description"]');
  viewEventStatus = viewElement.querySelector('[data-calendar="event_status"]');
  viewAmount = viewElement.querySelector('[data-calendar="event_amount"]');
  viewUId = viewElement.querySelector('[data-calendar="event_uId"]');
  viewService = viewElement.querySelector('[data-calendar="event_service"]');
  viewStartDate = viewElement.querySelector('[data-calendar="event_start_date"]');
  viewEndDate = viewElement.querySelector('[data-calendar="event_end_date"]');
};

// Format FullCalendar responses
var formatArgs = function formatArgs(res) {
  data.id = res.id;
  data.eventName = res.title;
  data.eventStatus = res.status;
  data.startDate = res.startStr;
  data.endDate = res.endStr;
  data.amount = res.amount;
  data.uId = res.uId;
  data.service = res.service;
  data.patientName = res.patientName;
};

// Initialize popovers --- more info: https://getbootstrap.com/docs/4.0/components/popovers/
var initPopovers = function initPopovers(element) {
  hidePopovers();

  // Generate popover content
  var startDate = data.allDay ? moment(data.startDate).format('Do MMM, YYYY') : moment(data.startDate).format('Do MMM, YYYY - h:mm a');
  var endDate = data.allDay ? moment(data.endDate).format('Do MMM, YYYY') : moment(data.endDate).format('Do MMM, YYYY - h:mm a');
  var popoverHtml = '<div class="fw-bolder mb-2"><b>Patient:</b> ' + data.patientName + '</div><div class="fs-7"><span class="fw-bold">Start:</span> ' + startDate + '</div><div class="fs-7 mb-4"><span class="fw-bold">End:</span> ' + endDate + '</div>';

  // Popover options
  var options = {
    container: 'body',
    trigger: 'manual',
    boundary: 'window',
    placement: 'auto',
    dismiss: true,
    html: true,
    title: 'Appointment Details',
    content: popoverHtml
  };
};

// Hide active popovers
var hidePopovers = function hidePopovers() {
  if (popoverState) {
    popover.dispose();
    popoverState = false;
  }
};

// Handle view event
var handleViewEvent = function handleViewEvent() {
  $('.fc-popover').addClass('hide');
  viewModal.show();

  // Detect all day event
  var eventNameMod;
  var startDateMod;
  var endDateMod;
  var book = $('#bookCalenderConst').val();
  var checkIn = $('#checkInCalenderConst').val();
  var checkOut = $('#checkOutCalenderConst').val();
  var cancel = $('#cancelCalenderConst').val();
  eventNameMod = '';
  startDateMod = moment(data.startDate).utc().format('Do MMM, YYYY - h:mm A');
  endDateMod = moment(data.endDate).utc().format('Do MMM, YYYY - h:mm A');
  viewEndDate.innerText = ': ' + endDateMod;
  viewStartDate.innerText = ': ' + startDateMod;

  // Populate view data
  viewEventName.innerText = 'Patient: ' + data.patientName;
  $(viewEventStatus).empty();
  $(viewEventStatus).append("\n<option class=\"booked\" disabled value=\"".concat(book, "\" ").concat(data.eventStatus == book ? 'selected' : '', ">").concat(Lang.get('js.booked'), "</option>\n<option value=\"").concat(checkIn, "\" ").concat(data.eventStatus == checkIn ? 'selected' : '', " ").concat(data.eventStatus == checkIn ? 'selected' : '', "\n    ").concat(data.eventStatus == cancel || data.eventStatus == checkOut ? 'disabled' : '', ">").concat(Lang.get('js.check_in'), "</option>\n<option value=\"").concat(checkOut, "\" ").concat(data.eventStatus == checkOut ? 'selected' : '', "\n    ").concat(data.eventStatus == cancel || data.eventStatus == book ? 'disabled' : '', ">").concat(Lang.get('js.check_out'), "</option>\n<option value=\"").concat(cancel, "\" ").concat(data.eventStatus == cancel ? 'selected' : '', " ").concat(data.eventStatus == checkIn ? 'disabled' : '', "\n   ").concat(data.eventStatus == checkOut ? 'disabled' : '', ">").concat(Lang.get('js.cancelled'), "</option>\n"));
  $(viewEventStatus).val(data.eventStatus).trigger('change');
  viewAmount.innerText = addCommas(data.amount);
  viewUId.innerText = data.uId;
  viewService.innerText = data.service;
};
listenChange('.doctor-apptnt-calendar-status-change', function () {
  if (!$(this).val()) {
    return false;
  }
  var appointmentStatus = $(this).val();
  var appointmentId = appointmentStatusId;
  if (parseInt(appointmentStatus) === data.eventStatus) {
    return false;
  }
  $.ajax({
    url: route('doctors.change-status', appointmentId),
    type: 'POST',
    data: {
      appointmentId: appointmentId,
      appointmentStatus: appointmentStatus
    },
    success: function success(result) {
      displaySuccessMessage(result.message);
      $('#doctorAppointmentCalendarModal').modal('hide');
      doctorAppointmentCalendar.refetchEvents();
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/doctor_appointments/doctor_appointments.js":
/*!************************************************************************!*\
  !*** ./resources/assets/js/doctor_appointments/doctor_appointments.js ***!
  \************************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadDoctorAppointmentFilterDate)

var doctorAppointmentFilterDate = '#doctorPanelAppointmentDate';
Livewire.hook("element.init", function () {
  loadDoctorAppointmentFilterDate();
  if ($('#doctorPanelPaymentType').length) {
    $('#doctorPanelPaymentType').select2();
  }
  if ($('#doctorPanelAppointmentStatus').length) {
    $('#doctorPanelAppointmentStatus').select2();
  }
  if ($('.appointment-status').length) {
    $('.appointment-status').select2();
  }
  if ($('.payment-status').length) {
    $('.payment-status').select2();
  }
});
function loadDoctorAppointmentFilterDate() {
  if (!$(doctorAppointmentFilterDate).length) {
    return;
  }
  var timeRange = $('#doctorPanelAppointmentDate');
  var doctorAppointmentStart = moment().startOf('week');
  var doctorAppointmentEnd = moment().endOf('week');
  function cb(doctorAppointmentStart, doctorAppointmentEnd) {
    $('#doctorPanelAppointmentDate').val(doctorAppointmentStart.format('MM/DD/YYYY') + ' - ' + doctorAppointmentEnd.format('MM/DD/YYYY'));
  }
  timeRange.daterangepicker({
    startDate: doctorAppointmentStart,
    endDate: doctorAppointmentEnd,
    opens: 'left',
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get('js.custom'),
      applyLabel: Lang.get('js.apply'),
      cancelLabel: Lang.get('js.cancel'),
      fromLabel: Lang.get('js.from'),
      toLabel: Lang.get('js.to'),
      monthNames: [Lang.get('js.jan'), Lang.get('js.feb'), Lang.get('js.mar'), Lang.get('js.apr'), Lang.get('js.may'), Lang.get('js.jun'), Lang.get('js.jul'), Lang.get('js.aug'), Lang.get('js.sep'), Lang.get('js.oct'), Lang.get('js.nov'), Lang.get('js.dec')],
      daysOfWeek: [Lang.get('js.sun'), Lang.get('js.mon'), Lang.get('js.tue'), Lang.get('js.wed'), Lang.get('js.thu'), Lang.get('js.fri'), Lang.get('js.sat')]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get('js.today'), [moment(), moment()]), Lang.get('js.yesterday'), [moment().subtract(1, 'days'), moment().subtract(1, 'days')]), Lang.get('js.this_week'), [moment().startOf('week'), moment().endOf('week')]), Lang.get('js.last_30_days'), [moment().subtract(29, 'days'), moment()]), Lang.get('js.this_month'), [moment().startOf('month'), moment().endOf('month')]), Lang.get('js.last_month'), [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')])
  }, cb);
  cb(doctorAppointmentStart, doctorAppointmentEnd);
  timeRange.on("apply.daterangepicker", function (ev, picker) {
    Livewire.dispatch('changeDateFilter', {
      date: $(this).val()
    });
  });
}
listenChange('.doctor-appointment-status-change', function () {
  var doctorAppointmentStatus = $(this).val();
  var doctorAppointmentId = $(this).attr('data-id');
  var doctorAppointmentCurrentData = $(this);
  $.ajax({
    url: route('doctors.change-status', doctorAppointmentId),
    type: 'POST',
    data: {
      appointmentId: doctorAppointmentId,
      appointmentStatus: doctorAppointmentStatus
    },
    success: function success(result) {
      $(doctorAppointmentCurrentData).children('option.booked').addClass('hide');
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
      setTimeout(function () {
        location.reload();
      }, 2000);
    }
  });
});
listenChange('.doctor-apptment-change-payment-status', function () {
  var doctorApptmentPaymentStatus = $(this).val();
  var doctorApptmentAppointmentId = $(this).attr('data-id');
  $('#doctorAppointmentPaymentStatusModal').modal('show').appendTo('body');
  $('#doctorAppointmentPaymentStatus').val(doctorApptmentPaymentStatus);
  $('#doctorAppointmentId').val(doctorApptmentAppointmentId);
});
listenSubmit('#doctorAppointmentPaymentStatusForm', function (event) {
  event.preventDefault();
  var paymentStatus = $('#doctorAppointmentPaymentStatus').val();
  var appointmentId = $('#doctorAppointmentId').val();
  var paymentMethod = $('#doctorPaymentType').val();
  $.ajax({
    url: route('doctors.change-payment-status', appointmentId),
    type: 'POST',
    data: {
      appointmentId: appointmentId,
      paymentStatus: paymentStatus,
      paymentMethod: paymentMethod,
      loginUserId: currentLoginUserId
    },
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#doctorAppointmentPaymentStatusModal').modal('hide');
        location.reload();
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenChange('#doctorPanelPaymentType', function () {
  Livewire.dispatch("changeDateFilter", {
    date: $('#doctorPanelAppointmentDate').val()
  });
  Livewire.dispatch("changePaymentTypeFilter", {
    type: $(this).val()
  });
});
listenChange('#doctorPanelAppointmentStatus', function () {
  Livewire.dispatch("changeDateFilter", {
    date: $('#doctorPanelAppointmentDate').val()
  });
  Livewire.dispatch("changeStatusFilter", {
    status: $(this).val()
  });
});
listenClick('#doctorPanelApptmentResetFilter', function () {
  $('#doctorPanelPaymentType').val(0).trigger('change');
  $('#doctorPanelAppointmentStatus').val(1).trigger('change');
  doctorAppointmentFilterDate.data('daterangepicker').setStartDate(moment().startOf('week').format('MM/DD/YYYY'));
  doctorAppointmentFilterDate.data('daterangepicker').setEndDate(moment().endOf('week').format('MM/DD/YYYY'));
  hideDropdownManually($('#doctorPanelApptFilterBtn'), $('.dropdown-menu'));
});
listenClick('#doctorPanelApptResetFilter', function () {
  $('#doctorPanelPaymentType').val(0).trigger('change');
  $('#doctorPanelAppointmentStatus').val(1).trigger('change');
  $('#doctorPanelAppointmentDate').data('daterangepicker').setStartDate(moment().startOf('week').format('MM/DD/YYYY'));
  $('#doctorPanelAppointmentDate').data('daterangepicker').setEndDate(moment().endOf('week').format('MM/DD/YYYY'));
  hideDropdownManually($('#doctorPanelApptFilterBtn'), $('.dropdown-menu'));
});

/***/ }),

/***/ "./resources/assets/js/doctor_holiday/Create_edit.js":
/*!***********************************************************!*\
  !*** ./resources/assets/js/doctor_holiday/Create_edit.js ***!
  \***********************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", loadDoctorData);
function loadDoctorData() {
  loadDoctorDate();
}
function loadDoctorDate() {
  var lang = $(".currentLanguage").val();
  $("#doctorHolidayDate").flatpickr({
    locale: lang,
    minDate: new Date().fp_incr(1),
    disableMobile: true
  });
  listenClick(".doctor-holiday-delete-btn", function (event) {
    var holidayRecordId = $(event.currentTarget).attr("data-id");
    deleteItem(route("holidays.destroy", holidayRecordId), Lang.get("js.holiday"));
  });
}

/***/ }),

/***/ "./resources/assets/js/doctor_holiday/doctor_holiday.js":
/*!**************************************************************!*\
  !*** ./resources/assets/js/doctor_holiday/doctor_holiday.js ***!
  \**************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadDoctorHoliday)

var startDate = moment().startOf("week");
var endDate = moment().endOf("week");
Livewire.hook("element.init", function () {
  loadDoctorHoliday();
  if ($("#doctorHolidayStatus").length) {
    $("#doctorHolidayStatus").select2();
  }
  if (startDate != undefined && endDate != undefined) {
    cb(startDate, endDate);
  }
});
function loadDoctorHoliday() {
  // let lang = $(".currentLanguage").val();
  // $("#doctorHolidayDate").flatpickr({
  //     locale: lang,
  //     minDate: new Date().fp_incr(1),
  //     disableMobile: true,
  // });

  // listenClick(".doctor-holiday-delete-btn", function (event) {
  //     let holidayRecordId = $(event.currentTarget).attr("data-id");
  //     deleteItem(
  //         route("holidays.destroy", holidayRecordId),
  //         Lang.get("js.holiday")
  //     );
  // });

  if (!$("#doctorHolidayDateFilter").length) {
    return;
  }
  // let startDate = moment().startOf("week");
  // let endDate = moment().endOf("week");

  var datePicker = $("#doctorHolidayDateFilter").daterangepicker({
    startDate: startDate,
    endDate: endDate,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  // cb
  );
  //cb(startDate, endDate);

  datePicker.on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    startDate = picker.startDate;
    endDate = picker.endDate;
    // Livewire.dispatch("changeDateFilter", { date: $(this).val() });
  });
}
function cb(start, end) {
  $("#doctorHolidayDateFilter").val(start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"));
}
listenChange("#doctorHolidayStatus", function () {
  $("#doctorHolidayStatus").val($(this).val());
  Livewire.dispatch("changeStatusFilter", $(this).val());
});

// listenChange('#holidayDateFilter,#doctorHolidayDateFilter', function () {
//     Livewire.dispatch("changeDateFilter", { date: $(this).val(),});
// })

listenClick(".holiday-delete-btn", function (event) {
  var holidayRecordId = $(event.currentTarget).attr("data-id");
  deleteItem(route("doctors.holiday-destroy", holidayRecordId), Lang.get("js.holiday"));
});

// listenClick('#holidayDateResetFilter', function () {
//     $('#holidayDateFilter').data('daterangepicker').setStartDate(moment().startOf('week').format('DD/MM/YYYY'))
//     $('#holidayDateFilter').data('daterangepicker').setEndDate(moment().endOf('week').format('DD/MM/YYYY'))
//     hideDropdownManually($('#holidayFilterBtn'), $('.dropdown-menu'));
// })

// listenClick('#doctorHolidayResetFilter', function () {
//     $('#doctorHolidayDateFilter').data('daterangepicker').setStartDate(moment().startOf('week').format('DD/MM/YYYY'))
//     $('#doctorHolidayDateFilter').data('daterangepicker').setEndDate(moment().endOf('week').format('DD/MM/YYYY'))
//     hideDropdownManually($('#doctorHolidayFilterBtn'), $('.dropdown-menu'));
// })

/***/ }),

/***/ "./resources/assets/js/doctor_holiday/holiday.js":
/*!*******************************************************!*\
  !*** ./resources/assets/js/doctor_holiday/holiday.js ***!
  \*******************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadHoliday)
var Start = moment().startOf("week");
var End = moment().endOf("week");
Livewire.hook("element.init", function () {
  loadHoliday();
  if (Start != undefined && End != undefined) {
    cb(Start, End);
  }
});
function loadHoliday() {
  if (!$("#holidayDateFilter").length) {
    return;
  }

  // let Start = moment().startOf("week");
  // let End = moment().endOf("week");

  var holidayPcker = $("#holidayDateFilter").daterangepicker({
    startDate: Start,
    endDate: End,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  // cb
  );

  // cb(Start, End);

  holidayPcker.on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    Start = picker.startDate;
    End = picker.endDate;
    // Livewire.dispatch("changeDateFilter", { date: $(this).val() });
  });
}
function cb(start, end) {
  $("#holidayDateFilter").val(start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"));
}
listenClick(".holiday-delete-btn", function (event) {
  var holidayRecordId = $(event.currentTarget).attr("data-id");
  deleteItem(route("doctors.holiday-destroy", holidayRecordId), Lang.get("js.holiday"));
});

/***/ }),

/***/ "./resources/assets/js/doctor_sessions/create-edit.js":
/*!************************************************************!*\
  !*** ./resources/assets/js/doctor_sessions/create-edit.js ***!
  \************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadDoctorSessionData);
function loadDoctorSessionData() {
  var doctorSessionIsEdit = $('#doctorSessionIsEdit').val();
  if (!doctorSessionIsEdit == true) {
    $('.startTimeSlot').prop('disabled', true);
    $('.endTimeSlot').prop('disabled', true);
  }
  var lang = $('.currentLanguage').val();
  $('#addHolidayBtn').flatpickr({
    "locale": lang,
    disableMobile: true,
    minDate: new Date()
  });
  $('select[name^="startTimes"]').each(function () {
    var selectedIndex = $(this)[0].selectedIndex;
    var endSelectedIndex = $(this).closest('.add-slot').find('select[name^="endTimes"] option:selected')[0].index;
    var endTimeOptions = $(this).closest('.add-slot').find('select[name^="endTimes"] option');
    if (selectedIndex >= endSelectedIndex) {
      endTimeOptions.eq(selectedIndex + 1).prop('selected', true).trigger('change');
    }
    endTimeOptions.each(function (index) {
      if (index <= selectedIndex) {
        $(this).attr('disabled', true);
      } else {
        $(this).attr('disabled', false);
      }
    });
  });
  $('select[name^="endTimes"]').each(function () {
    var selectedIndex = $(this)[0].selectedIndex;
    var startTimeOptions = $(this).closest('.timeSlot').next().find('select[name^="startTimes"] option');
    startTimeOptions.each(function (index) {
      if (index <= selectedIndex) {
        $(this).attr('disabled', true);
      } else {
        $(this).attr('disabled', false);
      }
    });
  });
}
listenChange('#selGap', function () {
  $('.startTimeSlot').prop('disabled', false);
  $('.endTimeSlot').prop('disabled', false);
});
listenClick('.add-session-time', function () {
  var doctorSessionIsEdit = $('#doctorSessionIsEdit').val();
  if (!doctorSessionIsEdit == true) {
    if ($('#selGap').val() == '') {
      return false;
    }
  }
  var selectedIndex = 0;
  if ($(this).parent().prev().children('.session-times').find('.timeSlot:last-child').length > 0) {
    selectedIndex = $(this).parent().prev().children('.session-times').find('.timeSlot:last-child').children('.add-slot').find('select[name^="endTimes"] option:selected')[0].index;
  }
  var day = $(this).closest('.weekly-content').attr('data-day');
  var $ele = $(this);
  var weeklyEle = $(this).closest('.weekly-content');
  var gap = $('#selGap').val();
  var getSlotByGapUrl = $('#getSlotByGapUrl').val();
  $.ajax({
    url: getSlotByGapUrl,
    data: {
      gap: gap,
      day: day
    },
    success: function success(data) {
      weeklyEle.find('.unavailable-time').html('');
      weeklyEle.find('input[name="checked_week_days[]"').prop('checked', true).prop('disabled', false);
      $ele.closest('.weekly-content').find('.session-times').append(data.data);
      weeklyEle.find('select[data-control="select2"]').select2();
      var startTimeOptions = $('.add-session-time').parent().prev().children('.session-times').find('.timeSlot:last-child').children('.add-slot').find('select[name^="startTimes"] option');
      startTimeOptions.each(function (index) {
        if (index <= selectedIndex) {
          $(this).attr('disabled', true);
        } else {
          $(this).attr('disabled', false);
        }
      });
    }
  });
});
listenClick('.copy-btn', function () {
  $(this).closest('.copy-card').removeClass('show');
  $('.copy-dropdown').removeClass('show');
  var selectEle = $(this).closest('.weekly-content').find('.session-times').find('select');
  // check for slot is empty
  if (selectEle.length == 0) {
    $(this).closest('.menu-content').find('.copy-label .form-check-input:checked').each(function () {
      var weekEle = $(".weekly-content[data-day=\"".concat($(this).val(), "\"]"));
      $(weekEle).find('.session-times').html('');
      weekEle.find('.weekly-row').find('.unavailable-time').remove();
      weekEle.find('.weekly-row').append('<div class="unavailable-time">' + Lang.get('js.unavailable') + '</div>');
      var dayChk = $(weekEle).find('.weekly-row').find('input[name="checked_week_days[]"');
      dayChk.prop('checked', false).prop('disabled', true);
    });
  } else {
    selectEle.each(function () {
      $(this).select2('destroy');
    });
    var selects = $(this).closest('.weekly-content').find('.session-times').find('select');
    var $cloneEle = $(this).closest('.weekly-content').find('.session-times').clone();
    $(this).closest('.menu-content').find('.copy-label .form-check-input:checked').each(function () {
      var $cloneEle2 = $cloneEle;
      var currentDay = $(this).val();
      var weekEle = ".weekly-content[data-day=\"".concat(currentDay, "\"]");
      $cloneEle2.find('select[name^="startTimes"]').attr('name', "startTimes[".concat(currentDay, "][]"));
      $cloneEle2.find('select[name^="endTimes"]').attr('name', "endTimes[".concat(currentDay, "][]"));
      $(weekEle).find('.unavailable-time').html('');
      $cloneEle2.find('.error-msg').html('');
      $(weekEle).find('.session-times').html($cloneEle2.html());
      $(weekEle).find('.session-times select').select2();
      $(weekEle).find('input[name="checked_week_days[]"').prop('disabled', false).prop('checked', true);
      $(selects).each(function (i) {
        var select = this;
        $(weekEle).find('.session-times').find('select').eq(i).val($(select).val()).trigger('change');
      });
    });
    $(this).closest('.weekly-content').find('.session-times').find('select').each(function () {
      $(this).select2();
    });
    $('.copy-check-input').prop('checked', false);
  }
});
listenClick('.deleteBtn', function () {
  var selectedIndex = 0;
  if ($(this).closest('.timeSlot').prev().length > 0) {
    selectedIndex = $(this).closest('.timeSlot').prev().children('.add-slot').find('select[name^="endTimes"] option:selected')[0].index;
  }
  if ($(this).closest('.weekly-row').find('.session-times').find('select').length == 2) {
    var dayChk = $(this).closest('.weekly-row').find('input[name="checked_week_days[]"');
    dayChk.prop('checked', false).prop('disabled', true);
    $(this).closest('.weekly-row').append('<div class="unavailable-time">' + Lang.get('js.unavailable') + '</div>');
  }
  var startTimeOptions = $(this).closest('.timeSlot').next().children('.add-slot').find('select[name^="startTimes"] option');
  startTimeOptions.each(function (index) {
    if (index <= selectedIndex) {
      $(this).attr('disabled', true);
    } else {
      $(this).attr('disabled', false);
    }
  });
  $(this).parent().siblings('.error-msg').remove();
  $(this).parent().closest('.timeSlot').remove();
  $(this).parent().remove();
});
listenSubmit('#saveFormDoctor', function (e) {
  e.preventDefault();
  var checkedDayLength = $('input[name="checked_week_days[]"]:checked').length;
  if (!checkedDayLength) {
    displayErrorMessage('Please select any one day');
    return false;
  }
  $(".weekly-content").find('.error-msg').text('');
  $.ajax({
    url: $(this).attr('action'),
    type: 'POST',
    data: new FormData($(this)[0]),
    processData: false,
    contentType: false,
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        setTimeout(function () {
          location.href = $('#btnBack').attr('href');
        }, 2000);
      }
    },
    error: function error(result) {
      var _result$responseJSON$ = result.responseJSON.message,
        day = _result$responseJSON$.day,
        key = _result$responseJSON$.key;
      $(".weekly-content[data-day=\"".concat(day, "\"]")).find('.error-msg').text('');
      $(".weekly-content[data-day=\"".concat(day, "\"]")).find('.error-msg').eq(key).text('Slot timing is overlap with other slot timing');
    },
    complete: function complete() {}
  });
});
listenChange('select[name^="startTimes"]', function (e) {
  var selectedIndex = $(this)[0].selectedIndex;
  var endTimeOptions = $(this).closest('.add-slot').find('select[name^="endTimes"] option');
  var endSelectedIndex = $(this).closest('.add-slot').find('select[name^="endTimes"] option:selected')[0].index;
  if (selectedIndex >= endSelectedIndex) {
    endTimeOptions.eq(selectedIndex + 1).prop('selected', true).trigger('change');
  }
  endTimeOptions.each(function (index) {
    if (index <= selectedIndex) {
      $(this).attr('disabled', true);
    } else {
      $(this).attr('disabled', false);
    }
  });
});
listenChange('select[name^="endTimes"]', function (e) {
  var selectedIndex = $(this)[0].selectedIndex;
  var startTimeOptions = $(this).closest('.timeSlot').next().find('select[name^="startTimes"] option');
  startTimeOptions.each(function (index) {
    if (index <= selectedIndex) {
      $(this).attr('disabled', true);
    } else {
      $(this).attr('disabled', false);
    }
  });
});
listenClick('#addHolidayBtn', function () {
  var doctorSessionIsEdit = $('#doctorSessionIsEdit').val();
});

/***/ }),

/***/ "./resources/assets/js/doctor_sessions/doctor_sessions.js":
/*!****************************************************************!*\
  !*** ./resources/assets/js/doctor_sessions/doctor_sessions.js ***!
  \****************************************************************/
/***/ (() => {

listenClick('.doctor-session-delete-btn', function (event) {
  var doctorSessionRecordId = $(event.currentTarget).attr('data-id');
  var doctorSessionUrl = $('#doctorSessionUrl').val();
  deleteItem(doctorSessionUrl + '/' + doctorSessionRecordId, Lang.get('js.doctor_session'));
});

/***/ }),

/***/ "./resources/assets/js/doctors/create-edit.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/doctors/create-edit.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr/dist/l10n */ "./node_modules/flatpickr/dist/l10n/index.js");
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__);

document.addEventListener('DOMContentLoaded', loadDoctorData);
var isDefault = false;
var deletedQualifications = [];
var degree;
var university;
var year;
var updateId;
var primaryId;
var qualification = [];
var id = 1;
$('.showQualification').hide();
function loadDoctorData() {
  loadDoctorDate();
}
function loadDoctorDate() {
  var doctorDob = '.doctor-dob';
  var lang = $('.currentLanguage').val();
  $('.showQualification').slideUp();
  if (!$(doctorDob).length) {
    return;
  }
  $(doctorDob).flatpickr({
    "locale": lang,
    maxDate: new Date(),
    disableMobile: true
  });
  if (!$('#doctorCountryId').val()) {
    return;
  }
  $('#editDoctorCountryId').val($('#doctorCountryId').val()).trigger('change');
  setTimeout(function () {
    $('#editDoctorStateId').val($('#doctorStateId').val()).trigger('change');
  }, 400);
  setTimeout(function () {
    $('#editDoctorCityId').val($('#doctorCityId').val()).trigger('change');
  }, 7000);
}
listenClick('#addQualification', function () {
  isDefault = false;
  $('.degree').val('');
  $('.university').val('');
  $('.year').val('').trigger('change');
  $('.showQualification').slideToggle(500);
});
listenClick('#cancelQualification', function () {
  $('.showQualification').slideUp(500);
});
listenClick('#ResetForm', function () {
  window.location.href = route('doctors.index');
});
listenClick('#saveQualification', function (e) {
  e.preventDefault();
  degree = $('.degree').val();
  university = $('.university').val();
  year = $('.year').val();
  var existId = $('#doctorQualificationTbl tr:last-child td:first-child').data('value');
  existId++;
  if (existId) {
    id = existId;
  }
  var prepareData = {
    'id': primaryId,
    'degree': degree,
    'year': year,
    'university': university
  };
  var data = {
    'id': id,
    'degree': degree,
    'year': year,
    'university': university
  };
  var emptyDegree = $('.degree').val().trim().replace(/ \r\n\t/g, '') === '';
  var emptyUniversity = $('.university').val().trim().replace(/ \r\n\t/g, '') === '';
  var emptyYear = $('.year').val().trim().replace(/ \r\n\t/g, '') === '';
  if (emptyDegree) {
    displayErrorMessage(Lang.get('js.degree_required'));
    return false;
  } else if (emptyUniversity) {
    displayErrorMessage(Lang.get('js.university_required'));
    return false;
  } else if (emptyYear) {
    displayErrorMessage(Lang.get('js.year_required'));
    return false;
  }
  if (updateId == null) {
    qualification.push(prepareData);
  } else {
    qualification[updateId - 1] = prepareData;
  }
  var qualificationHtml = prepareTemplateRender('#qualificationTemplateData', data);
  if (isDefault == false) {
    $('tbody').append(qualificationHtml);
    id++;
  } else if (isDefault == true) {
    var _data = {
      'id': updateId,
      'degree': degree,
      'year': year,
      'university': university
    };
    var updateQualificationHtml = prepareTemplateRender('#qualificationTemplateData', _data);
    var table = $('table tbody');
    $(table).find('tr').each(function (i, v) {
      i = i + 1;
      if (i == updateId) {
        $('tbody').find(v).replaceWith(updateQualificationHtml);
      }
    });
  }
  $('.showQualification').slideUp(500);
  $('.degree').val('');
  $('.university').val('');
  $('.year').val('');
});
listenClick('.delete-btn-qualification', function (event) {
  $('.degree').val('');
  $('.university').val('');
  $('.year').val('').trigger('change');
  qualification.pop([0]);
  $('.showQualification').slideUp(500);
  var Ele = $(this);
  var qualificationID = $(this).attr('data-id');
  var header = Lang.get('js.qualification');
  swal({
    title: Lang.get('js.delete') + ' !',
    text: Lang.get('js.are_you_sure') + ' "' + header + '" ?',
    buttons: {
      confirm: Lang.get('js.yes'),
      cancel: Lang.get('js.no')
    },
    reverseButtons: true,
    icon: 'warning'
  }).then(function (result) {
    if (result == true) {
      deletedQualifications.push(qualificationID);
      $('#deletedQualifications').val(deletedQualifications);
      Ele.closest('tr')[0].remove();
      swal({
        icon: 'success',
        title: Lang.get('js.deleted'),
        text: header + Lang.get('js.has_been'),
        timer: 2000
      });
    }
  });
});
listenClick('.edit-btn-qualification', function () {
  $('.degree').val('');
  $('.university').val('');
  $('.year').val('');
  updateId = $(this).attr('data-id');
  primaryId = $(this).data('primary-id');
  var currentRow = $(this).closest('tr');
  var currentDegree = currentRow.find('td:eq(1)').text();
  var currentCollage = currentRow.find('td:eq(2)').text();
  var currentYear = currentRow.find('td:eq(3)').text();
  $('.degree').val(currentDegree);
  $('.university').val(currentCollage);
  $('.year').val(currentYear).trigger('change');
  isDefault = true;
  $('.showQualification').slideToggle(500);
});
listenSubmit('#editDoctorForm', function (e) {
  var twitterUrl = $('#twitterUrl').val();
  var linkedinUrl = $('#linkedinUrl').val();
  var instagramUrl = $('#instagramUrl').val();
  var twitterExp = new RegExp(/^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)twitter.[a-z]{2,3}\/?.*/i);
  var linkedinExp = new RegExp(/^(https?:\/\/)?((w{2,3}\.)?)linkedin\.[a-z]{2,3}\/?.*/i);
  var instagramExp = new RegExp(/^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)instagram.[a-z]{2,3}\/?.*/i);
  var twitterCheck = twitterUrl == '' ? true : twitterUrl.match(twitterExp) ? true : false;
  if (!twitterCheck) {
    displayErrorMessage(Lang.get('js.valid_twitter'));
    return false;
  }
  var linkedInCheck = linkedinUrl == '' ? true : linkedinUrl.match(linkedinExp) ? true : false;
  if (!linkedInCheck) {
    displayErrorMessage(Lang.get('js.valid_linkedin'));
    return false;
  }
  var instagramCheck = instagramUrl == '' ? true : instagramUrl.match(instagramExp) ? true : false;
  if (!instagramCheck) {
    displayErrorMessage(Lang.get('js.valid_instagram'));
    return false;
  }
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
  e.preventDefault();
  var doctorFormData = new FormData($(this)[0]);
  var editDoctorId = $('#editDoctorId').val();
  doctorFormData.append('qualifications', JSON.stringify(qualification));
  $.ajax({
    url: route('doctors.update', editDoctorId),
    type: 'POST',
    data: doctorFormData,
    contentType: false,
    processData: false,
    success: function success(result) {
      if (result.success) {
        window.location.href = route('doctors.index');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenChange('input[type=radio][name=gender]', function () {
  var file = $('#profilePicture').val();
  if (isEmpty(file)) {
    if (this.value == 1) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + manAvatar + ')');
    } else if (this.value == 2) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + womanAvatar + ')');
    }
  }
});
listenChange('#editDoctorCountryId', function () {
  var doctorIsEdit = $('#doctorIsEdit').val();
  $.ajax({
    url: route('get-state'),
    type: 'get',
    dataType: 'json',
    data: {
      data: $(this).val()
    },
    success: function success(data) {
      $('#editDoctorStateId').empty();
      $('#editDoctorCityId').empty();
      //
      $('#editDoctorStateId').append($('<option value=""></option>').text(Lang.get('js.select_state')));
      $('#editDoctorCityId').append($('<option value=""></option>').text(Lang.get('js.select_city')));
      $.each(data.data, function (i, v) {
        $('#editDoctorStateId').append($('<option></option>').attr('value', i).text(v));
      });
      if (doctorIsEdit && $('#doctorStateId').val()) {
        $('#stateId').val($('#doctorStateId').val()).trigger('change');
      }
    }
  });
});
listenChange('#editDoctorStateId', function () {
  var doctorIsEdit = $('#doctorIsEdit').val();
  $.ajax({
    url: route('get-city'),
    type: 'get',
    dataType: 'json',
    data: {
      state: $(this).val(),
      country: $('#editDoctorCountryId').val()
    },
    success: function success(data) {
      $('#editDoctorCityId').empty();
      $('#editDoctorCityId').append($('<option value=""></option>').text(Lang.get('js.select_city')));
      $.each(data.data, function (i, v) {
        $('#editDoctorCityId').append($('<option ></option>').attr('value', i).text(v));
      });
      if (doctorIsEdit && $('#doctorCityId').val()) {
        $('#cityId').val($('#doctorCityId').val()).trigger('change');
      }
    }
  });
});
if ($('#doctorIsEdit').val() && $('#doctorCountryId').val()) {
  $('#editDoctorCountryId').val($('#doctorCountryId').val()).trigger('change');
}
listenKeyup('#twitterUrl', function () {
  this.value = this.value.toLowerCase();
});
listenKeyup('#linkedinUrl', function () {
  this.value = this.value.toLowerCase();
});
listenKeyup('#instagramUrl', function () {
  this.value = this.value.toLowerCase();
});
listenSubmit('#createDoctorForm', function () {
  var twitterUrl = $('#twitterUrl').val();
  var linkedinUrl = $('#linkedinUrl').val();
  var instagramUrl = $('#instagramUrl').val();
  var twitterExp = new RegExp(/^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)twitter.[a-z]{2,3}\/?.*/i);
  var linkedinExp = new RegExp(/^(https?:\/\/)?((w{2,3}\.)?)linkedin\.[a-z]{2,3}\/?.*/i);
  var instagramExp = new RegExp(/^(https?:\/\/)?((m{1}\.)?)?((w{2,3}\.)?)instagram.[a-z]{2,3}\/?.*/i);
  var twitterCheck = twitterUrl == '' ? true : twitterUrl.match(twitterExp) ? true : false;
  if (!twitterCheck) {
    displayErrorMessage(Lang.get('js.valid_twitter'));
    return false;
  }
  var linkedInCheck = linkedinUrl == '' ? true : linkedinUrl.match(linkedinExp) ? true : false;
  if (!linkedInCheck) {
    displayErrorMessage(Lang.get('js.valid_linkedin'));
    return false;
  }
  var instagramCheck = instagramUrl == '' ? true : instagramUrl.match(instagramExp) ? true : false;
  if (!instagramCheck) {
    displayErrorMessage(Lang.get('js.valid_instagram'));
    return false;
  }
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenClick('.removeAvatarIcon', function () {
  $('#bgImage').css('background-image', '');
  $('#bgImage').css('background-image', 'url(' + backgroundImg + ')');
  $('#removeAvatar').remove();
});

/***/ }),

/***/ "./resources/assets/js/doctors/detail.js":
/*!***********************************************!*\
  !*** ./resources/assets/js/doctors/detail.js ***!
  \***********************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
document.addEventListener('DOMContentLoaded', loadDoctorShowApptmentFilterDate);
var doctorShowApptmentFilterDate = $('#doctorShowAppointmentDateFilter');
function loadDoctorShowApptmentFilterDate() {
  if (!$('#doctorShowAppointmentDateFilter').length) {
    return;
  }
  var doctorShowApptmentStart = moment().startOf('week');
  var doctorShowApptmentEnd = moment().endOf('week');
  function cb(start, end) {
    $('#doctorShowAppointmentDateFilter').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
  }
  $('#doctorShowAppointmentDateFilter').daterangepicker({
    startDate: doctorShowApptmentStart,
    endDate: doctorShowApptmentEnd,
    opens: 'left',
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get('js.custom'),
      applyLabel: Lang.get('js.apply'),
      cancelLabel: Lang.get('js.cancel'),
      fromLabel: Lang.get('js.from'),
      toLabel: Lang.get('js.to'),
      monthNames: [Lang.get('js.jan'), Lang.get('js.feb'), Lang.get('js.mar'), Lang.get('js.apr'), Lang.get('js.may'), Lang.get('js.jun'), Lang.get('js.jul'), Lang.get('js.aug'), Lang.get('js.sep'), Lang.get('js.oct'), Lang.get('js.nov'), Lang.get('js.dec')],
      daysOfWeek: [Lang.get('js.sun'), Lang.get('js.mon'), Lang.get('js.tue'), Lang.get('js.wed'), Lang.get('js.thu'), Lang.get('js.fri'), Lang.get('js.sat')]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get('js.today'), [moment(), moment()]), Lang.get('js.yesterday'), [moment().subtract(1, 'days'), moment().subtract(1, 'days')]), Lang.get('js.this_week'), [moment().startOf('week'), moment().endOf('week')]), Lang.get('js.last_30_days'), [moment().subtract(29, 'days'), moment()]), Lang.get('js.this_month'), [moment().startOf('month'), moment().endOf('month')]), Lang.get('js.last_month'), [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')])
  }, cb);
  cb(doctorShowApptmentStart, doctorShowApptmentEnd);
}
listenClick('.doctor-show-apptment-delete-btn', function (event) {
  var doctorShowApptmentRecordId = $(event.currentTarget).attr('data-id');
  var doctorShowApptmentUrl = !isEmpty($('#patientRoleDoctorDetail').val()) ? route('patients.appointments.destroy', doctorShowApptmentRecordId) : route('appointments.destroy', doctorShowApptmentRecordId);
  deleteItem(doctorShowApptmentUrl, 'Appointment');
});
listenChange('.doctor-show-apptment-status', function () {
  var doctorShowAppointmentStatus = $(this).val();
  var doctorShowAppointmentId = $(this).attr('data-id');
  var currentData = $(this);
  $.ajax({
    url: route('change-status', doctorShowAppointmentId),
    type: 'POST',
    data: {
      appointmentId: doctorShowAppointmentId,
      appointmentStatus: doctorShowAppointmentStatus
    },
    success: function success(result) {
      $(currentData).children('option.booked').addClass('hide');
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    }
  });
});
listenChange('#doctorShowAppointmentDateFilter', function () {
  Livewire.dispatch('changeDateFilter', $(this).val());
});
listenChange('#doctorShowAppointmentStatus', function () {
  Livewire.dispatch('changeDateFilter', $('#doctorShowAppointmentDateFilter').val());
  Livewire.dispatch('changeStatusFilter', $(this).val());
});
listenClick('#doctorShowApptmentResetFilter', function () {
  $('#doctorShowAppointmentStatus').val(1).trigger('change');
  $('#doctorShowAppointmentDateFilter').val(moment().startOf('week').format('MM/DD/YYYY') + ' - ' + moment().endOf('week').format('MM/DD/YYYY')).trigger('change');
  Livewire.dispatch('refresh');
});
document.addEventListener('livewire:load', function () {
  window.livewire.hook('message.processed', function () {
    if ($('#doctorShowAppointmentStatus').length) {
      $('#doctorShowAppointmentStatus').select2();
    }
    if ($('.doctor-show-apptment-status').length) {
      $('.doctor-show-apptment-status').select2();
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/doctors/doctors.js":
/*!************************************************!*\
  !*** ./resources/assets/js/doctors/doctors.js ***!
  \************************************************/
/***/ (() => {

listenClick('#doctorResetFilter', function () {
  var firstDate = moment(moment().startOf('week'), "MM/DD/YYYY").day(0).format("MM/DD/YYYY");
  var lastDate = moment(moment().endOf('week'), "MM/DD/YYYY").day(6).format("MM/DD/YYYY");
  $('#doctorPanelAppointmentDate').val(firstDate + " - " + lastDate).trigger('change');
  $('#doctorPanelPaymentType').val(0).trigger('change');
  $('#doctorPanelAppointmentStatus').val(3).trigger('change');
  $('#doctorStatus').val(2).trigger('change');
  hideDropdownManually($('#doctorFilterBtn'), $('.dropdown-menu'));
});
listenChange('#doctorStatus', function () {
  Livewire.dispatch("changeStatusFilter", {
    value: $(this).val()
  });
});
Livewire.hook("element.init", function () {
  if ($('#doctorStatus').length) {
    $('#doctorStatus').select2();
  }
});
listenClick('.doctor-delete-btn', function () {
  var userId = $(this).attr('data-id');
  var deleteUserUrl = route('doctors.destroy', userId);
  deleteItem(deleteUserUrl, Lang.get('js.doctor'));
});
listenClick('.add-qualification', function () {
  var userId = $(this).attr('data-id');
  $('#qualificationID').val(userId);
  $('#qualificationModal').modal('show');
});
listenSubmit('#qualificationForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('add.qualification'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#year').val(null).trigger('change');
        $('#qualificationModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listen('hidden.bs.modal', '#qualificationModal', function () {
  resetModalForm('#qualificationForm');
  $('#year').val(null).trigger('change');
});
listenClick('.doctor-status', function (event) {
  var doctorRecordId = $(event.currentTarget).attr('data-id');
  $.ajax({
    type: 'PUT',
    url: route('doctor.status'),
    data: {
      id: doctorRecordId
    },
    success: function success(result) {
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    }
  });
});
listenClick('.doctor-email-verification', function (event) {
  var userId = $(event.currentTarget).attr('data-id');
  $.ajax({
    type: 'POST',
    url: route('resend.email.verification', userId),
    success: function success(result) {
      displaySuccessMessage(result.message);
      setTimeout(function () {
        window.location.reload();
        // Turbo.visit(window.location.href);
      }, 5000);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('#qualificationSaveBtn', function () {
  $('#qualificationForm').trigger('submit');
});
listenChange('.doctor-email-verified', function (e) {
  var recordId = $(e.currentTarget).attr('data-id');
  var value = $(this).is(':checked') ? 1 : 0;
  $.ajax({
    type: 'POST',
    url: route('emailVerified'),
    data: {
      id: recordId,
      value: value
    },
    success: function success(result) {
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    }
  });
});
Livewire.hook("element.init", function () {
  if ($('#enquiriesStatus').length) {
    $('#enquiriesStatus').select2();
  }
});

/***/ }),

/***/ "./resources/assets/js/fronts/appointments/book_appointment.js":
/*!*********************************************************************!*\
  !*** ./resources/assets/js/fronts/appointments/book_appointment.js ***!
  \*********************************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", loadFrontAppointmentData);
var frontTimezoneOffsetMinutes = new Date().getTimezoneOffset();
frontTimezoneOffsetMinutes = frontTimezoneOffsetMinutes === 0 ? 0 : -frontTimezoneOffsetMinutes;
var frontSelectedDate;
var frontCharge = "";
var frontPayableAmount = "";
var dateEle = "#templateAppointmentDate";
function loadFrontDateData() {
  if (!$("#templateAppointmentDate").length) {
    return;
  }
  $("#templateAppointmentDate").datepicker({
    language: "es-es",
    format: "yyyy-mm-dd",
    minDate: new Date(),
    startDate: new Date(),
    todayHighlight: true
  });
}
function loadFrontAppointmentData() {
  if (!$("#templateAppointmentDate").length) {
    return;
  }
  loadFrontDateData();
  var frontSelectedDate = $("#templateAppointmentDate").val();
  if (!($("#appointmentDoctorId").val() == "")) {
    $(dateEle).removeAttr("disabled");
    $.ajax({
      url: route("get-service"),
      type: "GET",
      data: {
        appointmentDoctorId: $("#appointmentDoctorId").val()
      },
      success: function success(result) {
        if (result.success) {
          $(dateEle).removeAttr("disabled");
          $("#FrontAppointmentServiceId").empty();
          $("#FrontAppointmentServiceId").append($('<option value=""></option>').text(Lang.get("js.select_service")));
          $.each(result.data, function (i, v) {
            $("#FrontAppointmentServiceId").append($("<option></option>").attr("value", v.id).text(v.name));
          });
        }
      }
    });
  }
  if (!($("#FrontAppointmentServiceId").val() == "") && $("#FrontAppointmentServiceId").length) {
    $.ajax({
      url: route("get-charge"),
      type: "GET",
      data: {
        chargeId: $("#FrontAppointmentServiceId").val()
      },
      success: function success(result) {
        if (result.success) {
          $("#payableAmountText").removeClass("d-none");
          $("#payableAmount").text(currencyIcon + " " + getFormattedPrice(result.data.charges));
          frontPayableAmount = result.data.charges;
          frontCharge = result.data.charges;
        }
      }
    });
  }
  if (!frontSelectedDate) {
    return false;
  }
  $.ajax({
    url: route("doctor-session-time"),
    type: "GET",
    data: {
      adminAppointmentDoctorId: $("#appointmentDoctorId").val(),
      date: frontSelectedDate,
      timezone_offset_minutes: frontTimezoneOffsetMinutes
    },
    success: function success(result) {
      if (result.success) {
        $(".appointment-slot-data").html("");
        $.each(result.data["slots"], function (index, value) {
          $(".no-time-slot").addClass("d-none");
          if (result.data["bookedSlot"] == null) {
            $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot" data-id="' + value + '">' + value + "</span>");
          } else {
            if ($.inArray(value, result.data["bookedSlot"]) !== -1) {
              $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot bookedSlot" data-id="' + value + '">' + value + "</span>");
            } else {
              $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot" data-id="' + value + '">' + value + "</span>");
            }
          }
        });
      }
    },
    error: function error(result) {
      $(".appointment-slot-data").html("");
      $(".book-appointment-message").css("display", "block");
      var response = '<div class="gen alert alert-danger">' + result.responseJSON.message + "</div>";
      $(".book-appointment-message").html(response).delay(5000).hide("slow");
    }
  });
}
listenChange("#isPatientAccount", function () {
  if (this.checked) {
    $(".name-details").addClass("d-none");
    $(".registered-patient").removeClass("d-none");
    $("#template-medical-email").keyup(function () {
      $("#patientName").val("");
      var email = $("#template-medical-email").val();
      $.ajax({
        url: route("get-patient-name"),
        type: "GET",
        data: {
          email: email
        },
        success: function success(result) {
          if (result.data) {
            $("#patientName").val(result.data);
          }
        }
      });
    });
  } else {
    $(".name-details").removeClass("d-none");
    $(".registered-patient").addClass("d-none");
  }
});
$(".no-time-slot").removeClass("d-none");
listenChange(dateEle, function () {
  frontSelectedDate = $(this).val();
  $.ajax({
    url: route("doctor-session-time"),
    type: "GET",
    data: {
      adminAppointmentDoctorId: $("#appointmentDoctorId").val(),
      date: frontSelectedDate,
      timezone_offset_minutes: frontTimezoneOffsetMinutes
    },
    success: function success(result) {
      if (result.success) {
        $(".appointment-slot-data").html("");
        $.each(result.data["slots"], function (index, value) {
          $(".no-time-slot").addClass("d-none");
          if (result.data["bookedSlot"] == null) {
            $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot" data-id="' + value + '">' + value + "</span>");
          } else {
            if ($.inArray(value, result.data["bookedSlot"]) !== -1) {
              $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot bookedSlot" data-id="' + value + '">' + value + "</span>");
            } else {
              $(".appointment-slot-data").append('<span class="badge badge-lg slots-item bg-success time-slot" data-id="' + value + '">' + value + "</span>");
            }
          }
        });
      }
    },
    error: function error(result) {
      $(".appointment-slot-data").html("");
      $(".book-appointment-message").css("display", "block");
      var response = '<div class="gen alert alert-danger">' + result.responseJSON.message + "</div>";
      $(".book-appointment-message").html(response).delay(5000).hide("slow");
      if ($(".no-time-slot").hasClass("d-none")) {
        $(".no-time-slot").removeClass("d-none");
      }
    }
  });
});
listenClick(".time-slot", function () {
  if ($(".time-slot").hasClass("activeSlot")) {
    $(".time-slot").removeClass("activeSlot");
    $(this).addClass("activeSlot");
  } else {
    $(this).addClass("activeSlot");
  }
  var fromToTime = $(this).attr("data-id").split("-");
  var fromTime = fromToTime[0];
  var toTime = fromToTime[1];
  $("#timeSlot").val("");
  $("#toTime").val("");
  $("#timeSlot").val(fromTime);
  $("#toTime").val(toTime);
});
var serviceIdExist = $("#FrontAppointmentServiceId").val();
listenChange("#appointmentDoctorId", function (e) {
  e.preventDefault();
  $("#payableAmountText").addClass("d-none");
  $("#chargeId").val("");
  $("#payableAmount").val("");
  $("#templateAppointmentDate").val("");
  $("#addFees").val("");
  $(".appointment-slot-data").html("");
  $(".no-time-slot").removeClass("d-none");
  $(dateEle).removeAttr("disabled");
  $.ajax({
    url: route("get-service"),
    type: "GET",
    data: {
      appointmentDoctorId: $(this).val()
    },
    success: function success(result) {
      if (result.success) {
        $(dateEle).removeAttr("disabled");
        $("#FrontAppointmentServiceId").empty();
        $("#FrontAppointmentServiceId").append($('<option value=""></option>').text(Lang.get("js.select_service")));
        $.each(result.data, function (i, v) {
          $("#FrontAppointmentServiceId").append($("<option></option>").attr("value", v.id).attr("selected", v.id == serviceIdExist).text(v.name));
        });
        if (serviceIdExist && $("#FrontAppointmentServiceId").val()) {
          $("#payableAmountText").removeClass("d-none");
        }
      }
    }
  });
});
listenChange("#FrontAppointmentServiceId", function () {
  if ($(this).val() == "") {
    $("#payableAmountText").addClass("d-none");
    return;
  }
  $.ajax({
    url: route("get-charge"),
    type: "GET",
    data: {
      chargeId: $(this).val()
    },
    success: function success(result) {
      if (result.success) {
        $("#payableAmountText").removeClass("d-none");
        $("#payableAmount").text(currencyIcon + " " + getFormattedPrice(result.data.charges));
        frontPayableAmount = result.data.charges;
        frontCharge = result.data.charges;
      }
    }
  });
});
listenSubmit("#frontAppointmentBook", function (e) {
  e.preventDefault();
  var firstName = $("#template-medical-first_name").val().trim();
  var lastName = $("#template-medical-last_name").val().trim();
  var email = $("#template-medical-email").val().trim();
  var doctor = $("#appointmentDoctorId").val().trim();
  var services = $("#FrontAppointmentServiceId").val().trim();
  var appointmentDate = $("#templateAppointmentDate").val().trim();
  var paymentType = $("#paymentMethod").val().trim();
  $(".book-appointment-message").css("display", "block");
  if (!$("#isPatientAccount").is(":checked")) {
    if (firstName == "") {
      response = '<div class="gen alert alert-danger">' + Lang.get("js.first_name_required") + "</div>";
      $(window).scrollTop($(".appointment-form").offset().top);
      $(".book-appointment-message").html(response).delay(5000).hide("slow");
      return false;
    }
    if (lastName == "") {
      response = '<div class="gen alert alert-danger">' + Lang.get("js.last_name_required") + "</div>";
      $(window).scrollTop($(".appointment-form").offset().top);
      $(".book-appointment-message").html(response).delay(5000).hide("slow");
      return false;
    }
  }
  if (email == "") {
    response = '<div class="gen alert alert-danger">' + Lang.get("js.email_required") + "</div>";
    $(".book-appointment-message").html(response).delay(5000).hide("slow");
    $(window).scrollTop($(".appointment-form").offset().top);
    return false;
  }
  if (doctor == "") {
    response = '<div class="gen alert alert-danger">' + Lang.get("js.doctor_required") + "</div>";
    $(".book-appointment-message").html(response).delay(5000).hide("slow");
    $(window).scrollTop($(".appointment-form").offset().top);
    return false;
  }
  if (services == "") {
    response = '<div class="gen alert alert-danger">' + Lang.get("js.service_required") + "</div>";
    $(".book-appointment-message").html(response).delay(5000).hide("slow");
    $(window).scrollTop($(".appointment-form").offset().top);
    return false;
  }
  if (appointmentDate == "") {
    response = '<div class="gen alert alert-danger">' + Lang.get("js.appointment_date_required") + "</div>";
    $(".book-appointment-message").html(response).delay(5000).hide("slow");
    $(window).scrollTop($(".appointment-form").offset().top);
    return false;
  }
  if (paymentType == "") {
    response = '<div class="gen alert alert-danger">' + Lang.get("js.payment_type_required") + "</div>";
    $(".book-appointment-message").html(response).delay(5000).hide("slow");
    $(window).scrollTop($(".appointment-form").offset().top);
    return false;
  }
  var btnSaveEle = $(this).find("#saveBtn");
  setFrontBtnLoader(btnSaveEle);
  var frontAppointmentFormData = new FormData($(this)[0]);
  frontAppointmentFormData.append("payable_amount", frontPayableAmount);
  var response = '<div class="alert alert-warning alert-dismissable"> ' + Lang.get("js.processing") + "</div>";
  jQuery(this).find(".book-appointment-message").html(response).show("slow");
  $.ajax({
    url: $(this).attr("action"),
    type: "POST",
    data: frontAppointmentFormData,
    processData: false,
    contentType: false,
    success: function success(result) {
      if (result.success) {
        var appointmentID = result.data.appointmentId;
        response = '<div class="gen alert alert-success">' + result.message + "</div>";
        $(".book-appointment-message").html(response).delay(5000).hide("slow");
        $(window).scrollTop($(".appointment-form").offset().top);
        $("#frontAppointmentBook")[0].reset();
        if (result.data.payment_type == manually) {
          // Turbo.visit(
          //     route("manually-payment", {
          //         appointmentId: appointmentID,
          //     })
          // );
          window.location.href = route("manually-payment", {
            appointmentId: appointmentID
          });
        }
        if (result.data.payment_type == authorizeMethod) {
          window.location.replace(route("authorize.init", {
            appointmentId: appointmentID
          }));
        }
        if (result.data.payment_type == paytmMethod) {
          window.location.replace(route("paytm.init", {
            appointmentId: appointmentID
          }));
        }
        if (result.data.payment_type == paypal) {
          $.ajax({
            type: "GET",
            url: route("paypal.init"),
            data: {
              appointmentId: appointmentID
            },
            success: function success(result) {
              if (result.status == 200) {
                var redirectTo = "";
                location.href = result.link;
                $.each(result.result.links, function (key, val) {
                  if (val.rel == "approve") {
                    redirectTo = val.href;
                  }
                });
                location.href = redirectTo;
              }
            },
            error: function error(result) {},
            complete: function complete() {}
          });
        }
        if (result.data.payment_type == stripeMethod) {
          var sessionId = result.data[0].sessionId;
          stripe.redirectToCheckout({
            sessionId: sessionId
          }).then(function (result) {
            manageAjaxErrors(result);
          });
        }
        if (result.data === manually) {
          setTimeout(function () {
            location.reload();
          }, 1200);
        }
      }
    },
    error: function error(result) {
      $(".book-appointment-message").css("display", "block");
      response = '<div class="gen alert alert-danger">' + result.responseJSON.message + "</div>";
      $(window).scrollTop($(".appointment-form").offset().top);
      $(".book-appointment-message").html(response).delay(5000).hide("slow");
    },
    complete: function complete() {
      setFrontBtnLoader(btnSaveEle);
    }
  });
});
listenClick(".show-more-btn", function () {
  if ($(".question").hasClass("d-none")) {
    $(".question").removeClass("d-none");
    $(".show-more-btn").html("show less");
  } else {
    $(".show-content").addClass("d-none");
    $(".show-more-btn").html("show more");
  }
});
window.setFrontBtnLoader = function (btnLoader) {
  if (btnLoader.attr("data-old-text")) {
    btnLoader.html(btnLoader.attr("data-old-text")).prop("disabled", false);
    btnLoader.removeAttr("data-old-text");
    return;
  }
  btnLoader.attr("data-old-text", btnLoader.text());
  btnLoader.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled", true);
};

/***/ }),

/***/ "./resources/assets/js/fronts/cms/create.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/fronts/cms/create.js ***!
  \**************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadFrontCMSData);
function loadFrontCMSData() {
  $('#cmsShortDescription').on('keyup', function () {
    $('#cmsShortDescription').attr('maxlength', 800);
  });
  $('#cmsShortDescription').attr('maxlength', 800);
  if (!$('#cmsTermConditionId').length) {
    return;
  }
  var quill1 = new Quill('#cmsTermConditionId', {
    modules: {
      toolbar: [[{
        header: [1, 2, false]
      }], ['bold', 'italic', 'underline'], ['image', 'code-block']]
    },
    placeholder: Lang.get('js.terms_conditions'),
    theme: 'snow' // or 'bubble'
  });
  quill1.on('text-change', function (delta, oldDelta, source) {
    if (quill1.getText().trim().length === 0) {
      quill1.setContents([{
        insert: ''
      }]);
    }
  });
  if (!$('#cmsPrivacyPolicyId').length) {
    return;
  }
  var quill2 = new Quill('#cmsPrivacyPolicyId', {
    modules: {
      toolbar: [[{
        header: [1, 2, false]
      }], ['bold', 'italic', 'underline'], ['image', 'code-block']]
    },
    placeholder: Lang.get('js.privacy_policy'),
    theme: 'snow' // or 'bubble'
  });
  quill2.on('text-change', function (delta, oldDelta, source) {
    if (quill2.getText().trim().length === 0) {
      quill2.setContents([{
        insert: ''
      }]);
    }
  });
  var element = document.createElement('textarea');
  element.innerHTML = $('#cmsTermConditionData').val();
  quill1.root.innerHTML = element.value;
  element.innerHTML = $('#cmsPrivacyPolicyData').val();
  quill2.root.innerHTML = element.value;
  listenSubmit('#addCMSForm', function () {
    var title = $('#aboutTitleId').val();
    var empty = title.trim().replace(/ \r\n\t/g, '') === '';
    var description = $('#cmsShortDescription').val();
    var empty2 = description.trim().replace(/ \r\n\t/g, '') === '';
    if (empty) {
      displayErrorMessage(Lang.get('js.title_no_white_space'));
      return false;
    }
    if (empty2) {
      displayErrorMessage(Lang.get('js.description_no_white_space'));
      return false;
    }
    if ($('#aboutExperience').val() === '') {
      displayErrorMessage(Lang.get('js.experience_required'));
      return false;
    }
    var element = document.createElement('textarea');
    var editor_content_1 = quill1.root.innerHTML;
    element.innerHTML = editor_content_1;
    var editor_content_2 = quill2.root.innerHTML;
    if (quill1.getText().trim().length === 0) {
      displayErrorMessage(Lang.get('js.Terms_Conditions_required'));
      return false;
    }
    if (quill2.getText().trim().length === 0) {
      displayErrorMessage(Lang.get('js.privacy_policy_required'));
      return false;
    }
    $('#termData').val(JSON.stringify(editor_content_1));
    $('#privacyData').val(JSON.stringify(editor_content_2));
  });
}

/***/ }),

/***/ "./resources/assets/js/fronts/enquiries/enquiry.js":
/*!*********************************************************!*\
  !*** ./resources/assets/js/fronts/enquiries/enquiry.js ***!
  \*********************************************************/
/***/ (() => {

listenClick('#enquiryResetFilter', function () {
  var allEnquiry = $('#allEnquiry').val();
  $('#enquiriesStatus').val(allEnquiry).trigger('change');
  hideDropdownManually($('#enquiryFilterBtn'), $('.dropdown-menu'));
});
listenChange('#enquiriesStatus', function () {
  Livewire.dispatch('changeStatusFilter', {
    value: $(this).val()
  });
});
listenClick('.enquiry-delete-btn', function () {
  var enquiryRecordId = $(this).attr('data-id');
  deleteItem(route('enquiries.destroy', enquiryRecordId), Lang.get('js.enquiry'));
});

/***/ }),

/***/ "./resources/assets/js/fronts/front_home/front-home.js":
/*!*************************************************************!*\
  !*** ./resources/assets/js/fronts/front_home/front-home.js ***!
  \*************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadFrontHomeData);
function loadFrontHomeData() {
  var frontAppointmentDate = '#frontAppointmentDate';
  if (!$(frontAppointmentDate).length) {
    return;
  }
  $(frontAppointmentDate).datepicker({
    format: 'yyyy-mm-dd',
    startDate: new Date(),
    todayHighlight: true
  });
}

/***/ }),

/***/ "./resources/assets/js/fronts/medical-contact/enquiry.js":
/*!***************************************************************!*\
  !*** ./resources/assets/js/fronts/medical-contact/enquiry.js ***!
  \***************************************************************/
/***/ (() => {

// listenSubmit('#enquiryForm', function (e) {
//     e.preventDefault()
//     let btnLoader = $(this).find('button[type="submit"]')
//     // setBtnLoader(btnLoader)
//     $.ajax({
//         url: route('enquiries.store'),
//         type: 'POST',
//         data: $(this).serialize(),
//         success: function (result) {
//             if (result.success) {
//              
//                 $('#enquiryForm')[0].reset()
//                
//             }
//         },
//         error: function (error) {
//             // toastr.error(error.responseJSON.message)
//         },
//     })
// })

/***/ }),

/***/ "./resources/assets/js/fronts/sliders/create-edit-slider.js":
/*!******************************************************************!*\
  !*** ./resources/assets/js/fronts/sliders/create-edit-slider.js ***!
  \******************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadSliderData);
function loadSliderData() {
  if (!$('#shortDescription').length) {
    return;
  }
  listenKeyup('#shortDescription', function () {
    $('#sliderShortDescription').attr('maxlength', 55);
  });
  if (!$('#sliderShortDescription').length) {
    return;
  }
  $('#sliderShortDescription').attr('maxlength', 55);
}

/***/ }),

/***/ "./resources/assets/js/fronts/sliders/slider.js":
/*!******************************************************!*\
  !*** ./resources/assets/js/fronts/sliders/slider.js ***!
  \******************************************************/
/***/ (() => {



/***/ }),

/***/ "./resources/assets/js/fronts/subscribers/create.js":
/*!**********************************************************!*\
  !*** ./resources/assets/js/fronts/subscribers/create.js ***!
  \**********************************************************/
/***/ (() => {

listenSubmit('#subscribeForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('subscribe.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        $('.subscribeForm-message').append('' + '<div class="gen alert alert-success">' + Lang.get("js.subscriber_creat") + '</div>').delay(5000);
        setTimeout(function () {
          $('.subscribeForm-message').empty();
          $('#subscribeForm')[0].reset();
        }, 3000);
      }
    },
    error: function error(_error) {
      $('.subscribeForm-message').append('' + '<div class="err alert alert-danger">' + Lang.get("js.email_already_exist") + '</div>').delay(5000);
      setTimeout(function () {
        $('.subscribeForm-message').empty();
        $('#subscribeForm')[0].reset();
      }, 3000);
    },
    complete: function complete() {}
  });
});

/***/ }),

/***/ "./resources/assets/js/fronts/subscribers/subscriber.js":
/*!**************************************************************!*\
  !*** ./resources/assets/js/fronts/subscribers/subscriber.js ***!
  \**************************************************************/
/***/ (() => {

listenClick('.subscriber-delete-btn', function () {
  var subscriberId = $(this).attr('data-id');
  deleteItem(route('subscribers.destroy', subscriberId), Lang.get('js.subscribers'));
});

/***/ }),

/***/ "./resources/assets/js/medicine_bills/medicine_bill.js":
/*!*************************************************************!*\
  !*** ./resources/assets/js/medicine_bills/medicine_bill.js ***!
  \*************************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", loadSaleMedicineCreate);
var uniquePrescriptionId = "";
function loadSaleMedicineCreate() {
  if (!$("#medicineUniqueId").length) {
    return;
  }
  $(".medicinePurchaseId").select2({
    width: "100%"
  });
  $(".medicine_bill_date").flatpickr({
    enableTime: true,
    defaultDate: new Date(),
    dateFormat: "Y-m-d H:i"
  });
  $(".edit_medicine_bill_date").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i"
  });
  $(".medicineBillExpiryDate").flatpickr({
    minDate: new Date(),
    dateFormat: "Y-m-d"
  });
  $(".medicine-payment-mode").select2({
    width: "100%"
  });
  $(".medicineBillCategoriesId").select2({
    width: "100%"
  });
}
listenChange(".medicineBillCategoriesId", function () {
  var categoryId = $(this).val();
  var currentRow = $(this).closest("tr");
  var medicineId = currentRow.find('.purchaseMedicineId');
  var medicineAvlQty = currentRow.find('.medicineTotalQuantity');
  var medicineSalePrice = currentRow.find('.medicineBill-sale-price');
  if (categoryId == "") {
    $(medicineId).find("option").remove();
    $(medicineId).append($("<option></option>").attr("placeholder", "").text(Lang.get("js.select_medicine")));
    $(medicineAvlQty).text('0');
    return false;
  }
  $.ajax({
    type: "get",
    url: route("get-medicine-category", categoryId),
    success: function success(result) {
      var array = result.data.medicine;
      $(medicineId).find("option").remove();
      $(medicineId).attr("required", true);
      $(medicineId).append($('<option value="">Select Medicine</option>'));
      $.each(array, function (key, value) {
        $(medicineId).append($('<option></option>').attr('value', key).text(value));
      });
      $(medicineAvlQty).text('0');
      $(medicineSalePrice).val('0.00');
    }
  });
});
listenChange(".medicinePurchaseId", function () {
  var currentRow = $(this).closest("tr");
  var medicineId = $(this).val();
  var uniqueId = $(this).attr("data-id");
  var salePriceId = currentRow.find(".medicineBill-sale-price");
  var QuantityPriceId = currentRow.find(".medicineTotalQuantity");
  if (medicineId == "" || medicineId == Lang.get("js.select_medicine")) {
    $(salePriceId).val("0.00");
    $(QuantityPriceId).text("0");
    return false;
  }
  $.ajax({
    type: "get",
    url: route("get-medicine", medicineId),
    success: function success(result) {
      $(salePriceId).val(result.data.selling_price.toFixed(2));
      var currentqty = currentRow.find(".medicineBill-quantity").val();
      var price = currentRow.find(".medicineBill-sale-price").val();
      var currentamount = parseFloat(price * currentqty);
      currentRow.find(".medicine-bill-amount").val(currentamount.toFixed(2));
      var taxEle = $(".medicineBill-tax");
      var elements = $(".medicine-bill-amount");
      var total = 0.0;
      var totalTax = 0;
      var netAmount = 0;
      var discount = 0;
      var amount = 0;
      for (var i = 0; i < elements.length; i++) {
        total += parseFloat(elements[i].value);
        discount = $(".medicineBill-discount").val();
        if (taxEle[i].value != 0 && taxEle[i].value != "") {
          totalTax += elements[i].value * taxEle[i].value / 100;
        } else {
          amount += parseFloat(elements[i].value);
        }
      }
      discount = discount == "" ? 0 : discount;
      netAmount = parseFloat(total) + parseFloat(totalTax);
      netAmount = parseFloat(netAmount) - parseFloat(discount);
      if (discount > total && $(this).hasClass("medicineBill-discount")) {
        discount = discount.slice(0, -1);
        displayErrorMessage(Lang.get("js.the_discount_shoul"));
        $("#discountAmount").val(discount);
        return false;
      }
      if (discount > total) {
        netAmount = 0;
      }
      $("#total").val(total.toFixed(2));
      $("#medicineTotalTaxId").val(totalTax.toFixed(2));
      $("#netAmount").val(netAmount.toFixed(2));
      $(QuantityPriceId).text(result.data.available_quantity);
    }
  });
});
listenClick(".add-medicine-btn-medicine-bill", function () {
  uniquePrescriptionId = $("#medicineUniqueId").val();
  var data = {
    medicinesCategories: JSON.parse($("#showMedicineCategoriesMedicineBill").val()),
    medicines: JSON.parse($(".associatePurchaseMedicines").val()),
    uniqueId: uniquePrescriptionId
  };
  var prescriptionMedicineHtml = prepareTemplateRender("#medicineBillTemplate", data);
  $(".medicine-bill-container").append(prescriptionMedicineHtml);
  dropdownToSelecte2(".medicinePurchaseId");
  dropdownToSelecteCategories2(".medicinebillCategories");
  expiryDateFlatePicker(".medicinebillCategories");
  $(".purchaseMedicineExpiryDate").flatpickr({
    minDate: new Date(),
    dateFormat: "Y-m-d"
  });
  uniquePrescriptionId++;
  $("#medicineUniqueId").val(uniquePrescriptionId);
});
var dropdownToSelecte2 = function dropdownToSelecte2(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.select_medicine'),
    width: "100%"
  });
};
var dropdownToSelecteCategories2 = function dropdownToSelecteCategories2(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.select_category'),
    width: "100%"
  });
};
var expiryDateFlatePicker = function expiryDateFlatePicker(selector) {
  $(".medicineBillExpiryDate").flatpickr({
    minDate: new Date(),
    dateFormat: "Y-m-d"
  });
};
listenKeyup(".medicineBill-quantity,.medicineBill-price,.medicineBill-tax,.medicineBill-discount,.medicineBill-sale-price", function () {
  var value = $(this).val();
  $(this).val(value.replace(/[^0-9\.]/g, ""));
  var currentRow = $(this).closest("tr");
  var currentqty = currentRow.find(".medicineBill-quantity").val();
  var price = currentRow.find(".medicineBill-sale-price").val();
  var currentamount = parseFloat(price * currentqty);
  currentRow.find(".medicine-bill-amount").val(currentamount.toFixed(2));
  var taxEle = $(".medicineBill-tax");
  var elements = $(".medicine-bill-amount");
  var total = 0.0;
  var totalTax = 0;
  var netAmount = 0;
  var discount = 0;
  var amount = 0;
  var qty = $(".medicineBill-quantity");
  var PreviousQty = $(".previous-quantity");
  for (var i = 0; i < elements.length; i++) {
    total += parseFloat(elements[i].value);
    discount = $(".medicineBill-discount").val();
    if ($("#medicineBillStatus").val() == 1) {
      if (parseInt(qty[i].value) > parseInt(PreviousQty[i].value)) {
        var qtyRollback = qty[i].value.slice(0, -1);
        currentRow.find(".medicineBill-quantity").val(qtyRollback);
        currentqty = currentRow.find(".medicineBill-quantity").val();
        price = currentRow.find(".medicineBill-sale-price").val();
        currentamount = parseFloat(price * currentqty);
        currentRow.find(".medicine-bill-amount").val(currentamount.toFixed(2));
        displayErrorMessage(Lang.get("js.update_quantity"));
        return false;
      }
    }
    if (taxEle[i].value != 0 && taxEle[i].value != "") {
      if (taxEle[i].value > 99) {
        var taxAmount = taxEle[i].value.slice(0, -1);
        currentRow.find(".medicineBill-tax").val(taxAmount);
        displayErrorMessage(Lang.get("js.tax_should_be"));
        $("#discountAmount").val(discount);
        return false;
      }
      totalTax += elements[i].value * taxEle[i].value / 100;
    } else {
      amount += parseFloat(elements[i].value);
    }
  }
  discount = discount == "" ? 0 : discount;
  netAmount = parseFloat(total) + parseFloat(totalTax);
  netAmount = parseFloat(netAmount) - parseFloat(discount);
  if (discount > total && $(this).hasClass("medicineBill-discount")) {
    discount = discount.slice(0, -1);
    displayErrorMessage(Lang.get("js.the_discount_shoul"));
    $("#discountAmount").val(discount);
    return false;
  }
  if (discount > total) {
    netAmount = 0;
  }
  $("#total").val(total.toFixed(2));
  $("#medicineTotalTaxId").val(totalTax.toFixed(2));
  $("#netAmount").val(netAmount.toFixed(2));
});
listenSubmit("#CreateMedicineBillForm", function (e) {
  e.preventDefault();
  var netAmount = "#netAmount";
  if ($("#total").val() < $("#discountAmount").val()) {
    displayErrorMessage(Lang.get("js.the_discount_shoul"));
    return false;
  } else if ($(netAmount).val() == null || $(netAmount).val() == "") {
    displayErrorMessage(Lang.get("js.net_amount_not_empty"));
    return false;
  } else if ($(netAmount).val() == 0) {
    displayErrorMessage(Lang.get("js.net_amount_not_zero"));
    return false;
  } else if ($(".medicineBill-quantity").val() == 0 || $(".medicineBill-quantity").val() == null || $(".medicineBill-quantity").val() == "") {
    displayErrorMessage(Lang.get("js.quantity_should"));
    return false;
  }
  $(this)[0].submit();
});
listenClick(".add-patient-modal", function () {
  $("#addPatientModal").appendTo("body").modal("show");
});
listenSubmit("#addPatientForm", function (e) {
  e.preventDefault();
  processingBtn("#addPatientForm", "#patientBtnSave", "loading");
  $("#patientBtnSave").attr("disabled", true);
  $.ajax({
    url: route("store.patient"),
    type: "POST",
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        $("#prescriptionPatientId").find("option").remove();
        $("#prescriptionPatientId").append($("<option></option>").attr("placeholder", "").text(Lang.get("js.select_patient")));
        $.each(result.data, function (i, v) {
          $("#prescriptionPatientId").append($("<option></option>").attr("value", i).text(v));
        });
        displaySuccessMessage(result.message);
        $("#addPatientModal").modal("hide");
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      $("#patientBtnSave").attr("disabled", false);
      processingBtn("#addPatientForm", "#patientBtnSave");
    }
  });
});
listen('hidden.bs.modal', "#addPatientModal", function () {
  resetModalForm("#addPatientForm", "#patientErrorsBox");
});
listenClick(".medicine-bill-delete-btn", function (event) {
  var id = $(event.currentTarget).attr("data-id");
  deleteItem(route("medicine-bills.destroy", id), Lang.get("js.medicine_bill"));
});
listenSubmit("#MedicinebillForm", function (e) {
  e.preventDefault();
  var netAmount = "#netAmount";
  if (parseFloat($("#total").val()) < parseFloat($("#discountAmount").val())) {
    displayErrorMessage(Lang.get("js.the_discount_shoul"));
    return false;
  } else if ($(netAmount).val() == null || $(netAmount).val() == "") {
    displayErrorMessage(Lang.get("js.net_amount_not_empty"));
    return false;
  } else if ($(netAmount).val() == 0) {
    displayErrorMessage(Lang.get("js.net_amount_not_zero"));
    return false;
  } else if ($(".medicineBill-quantity").val() == 0 || $(".medicineBill-quantity").val() == null || $(".medicineBill-quantity").val() == "") {
    displayErrorMessage(Lang.get("js.quantity_should"));
    return false;
  }
  $medicineBillId = $("#medicineBillId").val();
  $.ajax({
    url: route("medicine-bills.update", $medicineBillId),
    type: "post",
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        setTimeout(function () {
          // Turbo.visit(route("medicine-bills.index")); // true
          window.location.href = route("medicine-bills.index");
        }, 2000);
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick(".delete-medicine-bill-item", function () {
  var currentRow = $(this).closest("tr");
  var currentRowAmount = currentRow.find(".medicine-bill-amount").val();
  var currentRowTax = currentRow.find(".medicineBill-tax").val();
  var currentTaxAmount = parseFloat(currentRowAmount) * parseFloat(currentRowTax / 100);
  var updatedTax = parseFloat($("#medicineTotalTaxId").val()) - parseFloat(currentTaxAmount);
  $("#medicineTotalTaxId").val(updatedTax.toFixed(2));
  var updatedTotalAmount = parseFloat($("#total").val()) - parseFloat(currentRowAmount);
  $("#total").val(updatedTotalAmount.toFixed(2));
  var amountSubfromNetAmt = parseFloat(currentTaxAmount) + parseFloat(currentRowAmount);
  var updateNetAmount = parseFloat($("#netAmount").val()) - parseFloat(amountSubfromNetAmt);
  $("#netAmount").val(updateNetAmount.toFixed(2));
  $(this).parents("tr").remove();
});

/***/ }),

/***/ "./resources/assets/js/medicines/medicines.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/medicines/medicines.js ***!
  \****************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", loadMedicineCreateData);
"use strict";
function loadMedicineCreateData() {
  $('#medicineCategoryId,#medicineBrandId').select2({
    width: '100%'
  });
  listenClick(".showMedicineBtn", function (event) {
    event.preventDefault();
    var medicineId = $(event.currentTarget).attr("data-id");
    renderMedicineData(medicineId);
  });
  function renderMedicineData(id) {
    $.ajax({
      url: route("medicines.show.modal", id),
      type: "GET",
      success: function success(result) {
        if (result.success) {
          $("#showMedicineName").text(result.data.name);
          $("#showMedicineBrand").text(result.data.brand_name);
          $("#showMedicineCategory").text(result.data.category_name);
          $("#showMedicineSaltComposition").text(result.data.salt_composition);
          $("#showMedicineSellingPrice").text(result.data.selling_price);
          $("#showMedicineBuyingPrice").text(result.data.buying_price);
          $("#showMedicineQuanity").text(addCommas(result.data.quantity));
          $("#showMedicineAvailableQuanity").text(addCommas(result.data.available_quantity));
          $("#showMedicineSideEffects").text(result.data.side_effects);
          moment.locale($("#medicineLanguage").val());
          var createDate = moment(result.data.created_at);
          $("#showMedicineCreatedOn").text(createDate.fromNow());
          $("#showMedicineUpdatedOn").text(moment(result.data.updated_at).fromNow());
          $("#showMedicineDescription").text(result.data.description);
          setValueOfEmptySpan();
          $("#showMedicine").appendTo("body").modal("show");
        }
      },
      error: function error(result) {
        displayErrorMessage(result.responseJSON.message);
      }
    });
  }
}
listenClick(".deleteMedicineBtn", function (event) {
  var id = $(event.currentTarget).attr("data-id");
  medicineDeleteItem(route("check.use.medicine", id), Lang.get("js.medicine"));
});
window.medicineDeleteItem = function (url, header) {
  var tableId = null;
  var callFunction = null;
  $.ajax({
    url: url,
    type: "GET",
    success: function success(result) {
      if (result.success) {
        var popUpText = result.data.result == true ? Lang.get('js.the_medicine_already_in_use') : Lang.get('js.are_you_sure') + ' "' + header + '"?';
        swal({
          title: Lang.get('js.deleted'),
          text: popUpText,
          icon: 'warning',
          buttons: {
            confirm: Lang.get('js.yes'),
            cancel: Lang.get('js.no')
          }
        }).then(function (popResult) {
          if (popResult) {
            deleteMedicineAjax($("#indexMedicineUrl").val() + "/" + result.data.id, tableId = null, header, callFunction = null);
          }
        });
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
};
function deleteMedicineAjax(url) {
  var tableId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var header = arguments.length > 2 ? arguments[2] : undefined;
  var callFunction = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  $.ajax({
    url: url,
    type: "DELETE",
    dataType: "json",
    success: function success(obj) {
      if (obj.success && obj.data) {
        swal({
          title: obj.message,
          text: Lang.get('js.are_you_sure') + ' "' + header + '"?',
          icon: sweetAlertIcon,
          timer: 3000,
          buttons: {
            confirm: Lang.get('js.yes'),
            cancel: Lang.get('js.no')
          }
        }).then(function (result) {
          if (result) {
            $.ajax({
              url: url,
              type: "DELETE",
              dataType: "json",
              data: {
                canDeleteCheck: "yes"
              },
              success: function success(obj) {},
              error: function error(data) {
                swal({
                  title: "",
                  text: data.responseJSON.message,
                  confirmButtonColor: "#009ef7",
                  icon: "error",
                  timer: 5000,
                  buttons: {
                    confirm: Lang.get('js.ok')
                  }
                });
              }
            });
          }
        });
      }
      if (obj.success && !obj.data) {
        Livewire.dispatch("resetPage");
        swal({
          icon: "success",
          title: Lang.get('js.deleted'),
          confirmButtonColor: "#f62947",
          text: header + " " + Lang.get('js.has_been'),
          timer: 2000,
          buttons: {
            confirm: Lang.get('js.ok')
          }
        });
        if (callFunction) {
          eval(callFunction);
        }
      }
    },
    error: function error(data) {
      swal({
        title: "",
        text: data.responseJSON.message,
        confirmButtonColor: "#009ef7",
        icon: "error",
        timer: 5000,
        buttons: {
          confirm: Lang.get('js.ok')
        }
      });
    }
  });
}

/***/ }),

/***/ "./resources/assets/js/patient_visits/patient-visit.js":
/*!*************************************************************!*\
  !*** ./resources/assets/js/patient_visits/patient-visit.js ***!
  \*************************************************************/
/***/ (() => {



/***/ }),

/***/ "./resources/assets/js/patients/create-edit.js":
/*!*****************************************************!*\
  !*** ./resources/assets/js/patients/create-edit.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr/dist/l10n */ "./node_modules/flatpickr/dist/l10n/index.js");
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__);

document.addEventListener('DOMContentLoaded', loadPatientData);
function loadPatientData() {
  loadPatientDob();
  loadPatientCountry();
  loadPatientprofileCountry();
}
function loadPatientDob() {
  var patientDob = '.patient-dob';
  var lang = $('.currentLanguage').val();
  if (!$(patientDob).length) {
    return;
  }
  $(patientDob).flatpickr({
    "locale": lang,
    maxDate: new Date(),
    disableMobile: true
  });
}
function loadPatientCountry() {
  if (!$('#editPatientCountryId').length) {
    return;
  }
  $('#patientCountryId').val($('#editPatientCountryId').val()).trigger('change');
  setTimeout(function () {
    $('#patientStateId').val($('#editPatientStateId').val()).trigger('change');
  }, 400);
  setTimeout(function () {
    $('#patientCityId').val($('#editPatientCityId').val()).trigger('change');
  }, 700);
}
function loadPatientprofileCountry() {
  if (!$('#editPatientProfileCountryId').length) {
    return;
  }
  $('#patientProfileCountryId').val($('#editPatientProfileCountryId').val()).trigger('change');
  setTimeout(function () {
    $('#patientProfileStateId').val($('#editPatientProfileStateId').val()).trigger('change');
  }, 400);
  setTimeout(function () {
    $('#patientProfileCityId').val($('#editPatientProfileCityId').val()).trigger('change');
  }, 700);
}
listenChange('input[type=radio][name=gender]', function () {
  var file = $('#profilePicture').val();
  if (isEmpty(file)) {
    if (this.value == 1) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + manAvatar + ')');
    } else if (this.value == 2) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + womanAvatar + ')');
    }
  }
});
listenChange('#patientCountryId', function () {
  $('#patientStateId').empty();
  $('#patientCityId').empty();
  $.ajax({
    url: route('get-state'),
    type: 'get',
    dataType: 'json',
    data: {
      data: $(this).val()
    },
    success: function success(data) {
      $('#patientStateId').empty();
      $('#patientCityId').empty();
      $('#patientStateId').append($('<option value=""></option>').text('Select State'));
      $('#patientCityId').append($('<option value=""></option>').text('Select City'));
      $.each(data.data, function (i, v) {
        $('#patientStateId').append($('<option></option>').attr('value', i).text(v));
      });
    }
  });
});
listenChange('#patientProfileCountryId', function () {
  $('#patientProfileStateId').empty();
  $('#patientProfileCityId').empty();
  $.ajax({
    url: route('get-state'),
    type: 'get',
    dataType: 'json',
    data: {
      data: $(this).val()
    },
    success: function success(data) {
      $('#patientProfileStateId').empty();
      $('#patientProfileCityId').empty();
      $('#patientProfileStateId').append($('<option value=""></option>').text('Select State'));
      $('#patientProfileCityId').append($('<option value=""></option>').text('Select City'));
      $.each(data.data, function (i, v) {
        $('#patientProfileStateId').append($('<option></option>').attr('value', i).text(v));
      });
    }
  });
});
listenChange('#patientProfileStateId', function () {
  $('#patientProfileCityId').empty();
  $.ajax({
    url: route('get-city'),
    type: 'get',
    dataType: 'json',
    data: {
      state: $(this).val()
    },
    success: function success(data) {
      $('#patientProfileCityId').empty();
      $('#patientProfileCityId').append($('<option value=""></option>').text('Select City'));
      $.each(data.data, function (i, v) {
        $('#patientProfileCityId').append($('<option></option>').attr('value', i).text(v));
      });
      if ($('#patientProfileIsEdit').val() && $('#editPatientProfileCityId').val()) {
        $('#patientProfileCityId').val($('#editPatientProfileCityId').val()).trigger('change');
      }
    }
  });
});
listenChange('#patientStateId', function () {
  $('#patientCityId').empty();
  $.ajax({
    url: route('get-city'),
    type: 'get',
    dataType: 'json',
    data: {
      state: $(this).val()
    },
    success: function success(data) {
      $('#patientCityId').empty();
      $('#patientCityId').append($('<option value=""></option>').text('Select City'));
      $.each(data.data, function (i, v) {
        $('#patientCityId').append($('<option></option>').attr('value', i).text(v));
      });
      if ($('#patientIsEdit').val() && $('#editPatientCityId').val()) {
        $('#patientCityId').val($('#editPatientCityId').val()).trigger('change');
      }
    }
  });
});
listenSubmit('#createPatientForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenSubmit('#editPatientForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenClick('.removeAvatarIcon', function () {
  var backgroundImg = $('#patientBackgroundImg').val();
  $('#bgImage').css('background-image', '');
  $('#bgImage').css('background-image', 'url(' + backgroundImg + ')');
  $('#removeAvatar').addClass('hide');
  $('#tooltip287851').addClass('hide');
});

/***/ }),

/***/ "./resources/assets/js/patients/detail.js":
/*!************************************************!*\
  !*** ./resources/assets/js/patients/detail.js ***!
  \************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadPatientShowAppointmentDate)

var patientShowApptmentFilterDate = $("#patientShowPageAppointmentDate");
var patientShowApptmentStart = moment().startOf("week");
var patientShowApptmentEnd = moment().endOf("week");
Livewire.hook("element.init", function () {
  loadPatientShowAppointmentDate();
  if ($("#patientShowPageAppointmentStatus").length) {
    $("#patientShowPageAppointmentStatus").select2();
  }
  if ($(".patient-show-apptment-status-change").length) {
    $(".patient-show-apptment-status-change").select2();
  }
  if (patientShowApptmentStart != undefined && patientShowApptmentEnd != undefined) {
    cb(patientShowApptmentStart, patientShowApptmentEnd);
  }
});
function loadPatientShowAppointmentDate() {
  if (!$("#patientShowPageAppointmentDate").length) {
    return;
  }

  // let patientShowApptmentStart = moment().startOf("week");
  // let patientShowApptmentEnd = moment().endOf("week");

  $("#patientShowPageAppointmentDate").daterangepicker({
    startDate: patientShowApptmentStart,
    endDate: patientShowApptmentEnd,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  //cb
  );

  // cb(patientShowApptmentStart, patientShowApptmentEnd);
}
function cb(start, end) {
  $("#patientShowPageAppointmentDate").val(start.format("YYYY-MM-DD") + " - " + end.format("YYYY-MM-DD"));
}
listenClick(".patient-show-apptment-delete-btn", function (event) {
  var patientShowApptmentRecordId = $(event.currentTarget).attr("data-id");
  var patientShowApptmentUrl = !isEmpty($("#patientRolePatientDetail").val()) ? route("patients.appointments.destroy", patientShowApptmentRecordId) : route("appointments.destroy", patientShowApptmentRecordId);
  deleteItem(patientShowApptmentUrl, "Appointment");
});
listenChange(".patient-show-apptment-status-change", function () {
  var patientShowAppointmentStatus = $(this).val();
  var patientShowAppointmentId = $(this).attr("data-id");
  var currentData = $(this);
  $.ajax({
    url: route("change-status", patientShowAppointmentId),
    type: "POST",
    data: {
      appointmentId: patientShowAppointmentId,
      appointmentStatus: patientShowAppointmentStatus
    },
    success: function success(result) {
      $(currentData).children("option.booked").addClass("hide");
      Livewire.dispatch("refresh");
      displaySuccessMessage(result.message);
    }
  });
});
listenClick("#patientAppointmentResetFilter", function () {
  $("#patientShowPageAppointmentStatus").val(1).trigger("change");
  $("#patientShowPageAppointmentDate").val(moment().startOf("week").format("MM/DD/YYYY") + " - " + moment().endOf("week").format("MM/DD/YYYY")).trigger("change");
});
listenChange("#patientShowPageAppointmentDate", function () {
  Livewire.dispatch("changeDateFilter", {
    date: $(this).val()
  });
});
listenChange("#patientShowPageAppointmentStatus", function () {
  // Livewire.dispatch('changeDateFilter',
  //     $('#patientShowPageAppointmentDate').val())
  Livewire.dispatch("changeStatusFilter", {
    status: $(this).val()
  });
});

// document.addEventListener('livewire:load', function () {
//     window.livewire.hook('message.processed', () => {
//         if ($('#patientShowPageAppointmentStatus').length) {
//             $('#patientShowPageAppointmentStatus').select2()
//         }
//         if ($('.patient-show-apptment-status-change').length) {
//             $('.patient-show-apptment-status-change').select2()
//         }
//     })
// })

/***/ }),

/***/ "./resources/assets/js/patients/doctor-patient-appointment.js":
/*!********************************************************************!*\
  !*** ./resources/assets/js/patients/doctor-patient-appointment.js ***!
  \********************************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadDoctorPanelApptmentFilteDate);
var doctorPanelApptmentFilterDate = $('#doctorAppointmentDateFilter');
function loadDoctorPanelApptmentFilteDate() {
  if (!doctorPanelApptmentFilterDate.length) {
    return;
  }
  var doctorPanelApptmentStart = moment().startOf('week');
  var doctorPanelApptmentEnd = moment().endOf('week');
  function cb(start, end) {
    doctorPanelApptmentFilterDate.html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
  }
  doctorPanelApptmentFilterDate.daterangepicker({
    startDate: doctorPanelApptmentStart,
    endDate: doctorPanelApptmentEnd,
    ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'This Week': [moment().startOf('week'), moment().endOf('week')],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
  }, cb);
  cb(doctorPanelApptmentStart, doctorPanelApptmentEnd);
}
listenClick('.doctor-panel-delete-btn', function (event) {
  var doctorPanelApptmentRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('patients.appointments.destroy', doctorPanelApptmentRecordId), 'Appointment');
});
listenChange('.doctor-panel-status-change', function () {
  var appointmentStatus = $(this).val();
  var appointmentId = $(this).attr('data-id');
  var currentData = $(this);
  $.ajax({
    url: route('doctors.change-status', appointmentId),
    type: 'POST',
    data: {
      appointmentId: appointmentId,
      appointmentStatus: appointmentStatus
    },
    success: function success(result) {
      $(currentData).children('option.booked').addClass('hide');
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    }
  });
});
listenClick('#doctorPanelResetFilter', function () {
  $('#appointmentStatus').val(book).trigger('change');
  $('#doctorAppointmentDateFilter').val(moment().format('MM/DD/YYYY') + ' - ' + moment().format('MM/DD/YYYY')).trigger('change');
});

/***/ }),

/***/ "./resources/assets/js/patients/patients.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/patients/patients.js ***!
  \**************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadAppointmentFilterDate)

var patientFilterDate = "#patientDateFilter";
var patientStart = moment().subtract(100, "years");
var patientEnd = moment();
Livewire.hook("element.init", function () {
  loadAppointmentFilterDate();
  if (patientStart != undefined && patientEnd != undefined) {
    cb(patientStart, patientEnd);
  }
});
function loadAppointmentFilterDate() {
  if (!$(patientFilterDate).length) {
    return;
  }
  var timeRange = $("#patientDateFilter");
  // let patientStart = moment().subtract(100, "years");
  // let patientEnd = moment();

  timeRange.daterangepicker({
    startDate: patientStart,
    endDate: patientEnd,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.all"), [moment().subtract(100, "years"), moment()]), Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  //  cb
  );

  // cb(patientStart, patientEnd)

  timeRange.on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    patientStart = picker.startDate;
    patientEnd = picker.endDate;
  });
}
function cb(start, end) {
  $("#patientDateFilter").val(start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"));
}
listenClick(".patient-delete-btn", function () {
  var patientId = $(this).attr("data-id");
  deleteItem(route("patients.destroy", patientId), Lang.get("js.patient"));
});
listenChange(".patient-email-verified", function (e) {
  var patientRecordId = $(e.currentTarget).attr("data-id");
  var value = $(this).is(":checked") ? 1 : 0;
  $.ajax({
    type: "POST",
    url: route("emailVerified"),
    data: {
      id: patientRecordId,
      value: value
    },
    success: function success(result) {
      Livewire.dispatch("refresh");
      displaySuccessMessage(result.message);
    }
  });
});
listenClick(".patient-email-verification", function (event) {
  var userId = $(event.currentTarget).attr("data-id");
  var verificationUrl = $(event.currentTarget).attr("data-verification-url");
  $.ajax({
    type: "POST",
    url: verificationUrl || route("resend.email.verification", userId),
    // Fallback to original route
    success: function success(result) {
      displaySuccessMessage(result.message);
      setTimeout(function () {
        window.location.reload();
        // Turbo.visit(window.location.href);
      }, 5000);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/prescriptions/create-edit.js":
/*!**********************************************************!*\
  !*** ./resources/assets/js/prescriptions/create-edit.js ***!
  \**********************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadPrescriptionCreate);
var uniquePrescriptionId = 1;
function loadPrescriptionCreate() {
  if (!$('#prescriptionPatientId').length && !$('#editPrescriptionPatientId').length) {
    return;
  }
  $('#prescriptionPatientId,#editPrescriptionPatientId,#filter_status,#prescriptionDoctorId,#editPrescriptionDoctorId,#prescriptionTime,#prescriptionMedicineCategoryId,#prescriptionMedicineBrandId,.prescriptionMedicineId,.prescriptionMedicineMealId,#editPrescriptionTime').select2({
    width: '100%'
  });
  $('#prescriptionMedicineBrandId, #prescriptionMedicineBrandId').select2({
    width: '100%',
    dropdownParent: $('#add_new_medicine')
  });
  $('#prescriptionPatientId,#editPrescriptionPatientId').first().focus();
}
;
listenSubmit('#createPrescription, #editPrescription', function () {
  $('.btnPrescriptionSave').attr('disabled', true);
});
listenClick(".add-medicine", function () {
  $("#add_new_medicine").appendTo("body").modal("show");
});
listenSubmit('#createMedicineFromPrescription', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('prescription.medicine.store'),
    method: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      displaySuccessMessage(result.message);
      $('#add_new_medicine').modal('hide');
      $(".medicineTable").load(location.href + " .medicineTable");
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listen('hidden.bs.modal', '#add_new_medicine', function () {
  resetModalForm('#createMedicineFromPrescription', '#medicinePrescriptionErrorBox');
});
var dropdownToSelecte2 = function dropdownToSelecte2(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.select_medicine'),
    width: '100%'
  });
};
var dropdownToSelecteDuration2 = function dropdownToSelecteDuration2(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.select_duration'),
    width: '100%'
  });
};
var dropdownToSelecteInterVal = function dropdownToSelecteInterVal(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.dose_interval'),
    width: '100%'
  });
};
listenClick('.delete-prescription-medicine-item', function () {
  $(this).parents('tr').remove();
  // resetPrescriptionMedicineItemIndex()
});
listenClick('.add-medicine-btn', function () {
  uniquePrescriptionId++;
  $('#prescriptionUniqueId').val(uniquePrescriptionId);
  var data = {
    'medicines': JSON.parse($('.associatePrescriptionMedicines').val()),
    'meals': JSON.parse($('.associatePrescriptionMeals').val()),
    'doseDuration': JSON.parse($('.DoseDurationId').val()),
    'doseInterVal': JSON.parse($('.DoseInterValId').val()),
    'uniqueId': uniquePrescriptionId
  };
  var prescriptionMedicineHtml = prepareTemplateRender('#prescriptionMedicineTemplate', data);
  $('.prescription-medicine-container').append(prescriptionMedicineHtml);
  dropdownToSelecte2('.prescriptionMedicineId');
  dropdownToSelecte2('.prescriptionMedicineMealId');
  dropdownToSelecteDuration2('.DoseDurationIdTemplate');
  dropdownToSelecteInterVal('.DoseInterValIdTemplate');
});
var resetPrescriptionMedicineItemIndex = function resetPrescriptionMedicineItemIndex() {
  var index = 1;
  if (index - 1 == 0) {
    var data = {
      'medicines': JSON.parse($('.associatePrescriptionMedicines').val()),
      'meals': JSON.parse($('.associatePrescriptionMeals').val()),
      'doseDuration': JSON.parse($('.DoseDurationId').val()),
      'doseInterVal': JSON.parse($('.DoseInterValId').val()),
      'uniqueId': uniquePrescriptionId
    };
    var packageServiceItemHtml = prepareTemplateRender('#prescriptionMedicineTemplate', data);
    $('.prescription-medicine-container').append(packageServiceItemHtml);
    dropdownToSelecte2('.prescriptionMedicineId');
    dropdownToSelecte2('.prescriptionMedicineMealId');
    dropdownToSelecteDuration2('.DoseDurationIdTemplate');
    dropdownToSelecteInterVal('.DoseInterValIdTemplate');
    uniquePrescriptionId++;
  }
};
listenChange('.quantityget', function () {
  var medicineId = $(this).val();
  var currentRow = $(this).closest("tr");
  var uniqueId = $(this).attr("data-id");
  var salePriceId = currentRow.find('.quantityshow');
  var totalPriceId = currentRow.find('.totalqty');
  if (medicineId == "" || medicineId == Lang.get("js.select_medicine")) {
    $(totalPriceId).addClass("d-none");
    $(salePriceId).addClass("d-none");
    return false;
  }
  $.ajax({
    type: "get",
    url: route("get-medicine", medicineId),
    success: function success(result) {
      $(totalPriceId).removeClass("d-none");
      $(salePriceId).removeClass("d-none");
      $(totalPriceId).attr("class", "text-success totalqty");
      $('#quantityshow' + uniqueId).text(result.data.available_quantity);
      if ($(salePriceId).text() == null) {
        $(totalPriceId).attr("class", "text-success totalqty d-none");
      }
      if ($(salePriceId).text() != null) {
        $(totalPriceId).attr("class", "text-success totalqty");
        $(".extra-margin-tr").css("margin-top", "21px");
        $(".extrm").css("margin-top", "21px");
      }
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/prescriptions/prescriptions.js":
/*!************************************************************!*\
  !*** ./resources/assets/js/prescriptions/prescriptions.js ***!
  \************************************************************/
/***/ (() => {

listenClick('.delete-prescription-btn', function (event) {
  var prescriptionId = $(event.currentTarget).attr('data-id');
  deleteItem(route("prescriptions.destroy", prescriptionId), Lang.get('js.prescription'));
});
listenChange('.prescriptionStatus', function (event) {
  var prescriptionId = $(event.currentTarget).attr('data-id');
  prescriptionUpdateStatus(prescriptionId);
});
function prescriptionUpdateStatus(id) {
  $.ajax({
    url: route(prescriptionStatusRoute, id),
    method: 'post',
    cache: false,
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        hideDropdownManually($('#prescriptionFilterBtn'), $('#prescriptionFilter'));
      }
    }
  });
}
listenClick('#prescriptionResetFilter', function () {
  $('#prescriptionHead').val('2').trigger('change');
  hideDropdownManually($('#prescriptionFilterBtn'), $('.dropdown-menu'));
});
listenChange('#prescriptionHead', function () {
  Livewire.dispatch('changeFilter', {
    value: $(this).val()
  });
});

/***/ }),

/***/ "./resources/assets/js/profile/create-edit.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/profile/create-edit.js ***!
  \****************************************************/
/***/ (() => {

listenSubmit('#profileForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenClick('.removeAvatarIcon', function () {
  $('#bgImage').css('background-image', '');
  $('#bgImage').css('background-image', 'url(' + backgroundImg + ')');
  $('#removeAvatar').addClass('hide');
  $('#tooltip287851').addClass('hide');
});

/***/ }),

/***/ "./resources/assets/js/purchase-medicine/purchase-medicine.js":
/*!********************************************************************!*\
  !*** ./resources/assets/js/purchase-medicine/purchase-medicine.js ***!
  \********************************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", loadPurchaseMedicineCreate);
var uniquePrescriptionId = "";
function loadPurchaseMedicineCreate() {
  if (!$("#purchaseUniqueId").length) {
    return;
  }
  $(".purchaseMedicineExpiryDate").flatpickr({
    minDate: new Date(),
    dateFormat: "Y-m-d"
  });
  $("#paymentMode,#paymentMode2").select2({
    width: "100%"
  });
}
listenClick(".add-medicine-btn-purchase", function () {
  uniquePrescriptionId = $("#purchaseUniqueId").val();
  var data = {
    medicines: JSON.parse($(".associatePurchaseMedicines").val()),
    uniqueId: uniquePrescriptionId
  };
  var prescriptionMedicineHtml = prepareTemplateRender("#purchaseMedicineTemplate", data);
  $(".prescription-medicine-container").append(prescriptionMedicineHtml);
  dropdownToSelecte2(".purchaseMedicineId");
  $(".purchaseMedicineExpiryDate").flatpickr({
    minDate: new Date(),
    dateFormat: "Y-m-d"
  });
  uniquePrescriptionId++;
  $("#purchaseUniqueId").val(uniquePrescriptionId);
});
var dropdownToSelecte2 = function dropdownToSelecte2(selector) {
  $(selector).select2({
    placeholder: Lang.get('js.select_medicine'),
    width: "100%"
  });
};
listenChange(".purchaseMedicineId", function () {
  var medicineId = $(this).val();
  var uniqueId = $(this).attr("data-id");
  var salePriceId = "#sale_price" + uniqueId;
  var buyPriceId = "#purchase_price" + uniqueId;
  if (medicineId == "") {
    $(salePriceId).val("0.00");
    $(buyPriceId).val("0.00");
    return false;
  }
  $.ajax({
    type: "get",
    url: route("get-medicine", medicineId),
    success: function success(result) {
      $(salePriceId).val(result.data.selling_price.toFixed(2));
      $(buyPriceId).val(result.data.buying_price.toFixed(2));
    }
  });
});
listenKeyup(".purchase-quantity,.purchase-price,purchase-quantity,.purchase-tax,.purchase-discount", function () {
  var value = $(this).val();
  $(this).val(value.replace(/[^0-9\.]/g, ""));
  var currentRow = $(this).closest("tr");
  var currentqty = currentRow.find('.purchase-quantity').val();
  var price = currentRow.find('.purchase-price').val();
  var currentamount = parseFloat(price * currentqty);
  currentRow.find('.purchase-amount').val(currentamount.toFixed(2));
  var taxEle = $('.purchase-tax');
  var elements = $('.purchase-amount');
  var total = 0.00;
  var totalTax = 0;
  var netAmount = 0;
  var discount = 0;
  var amount = 0;
  for (var i = 0; i < elements.length; i++) {
    total += parseFloat(elements[i].value);
    discount = $('.purchase-discount').val();
    if (taxEle[i].value != 0 && taxEle[i].value != '') {
      if (taxEle[i].value > 99) {
        var taxAmount = taxEle[i].value.slice(0, -1);
        currentRow.find('.purchase-tax').val(taxAmount);
        displayErrorMessage(Lang.get("js.tax_should_be"));
        $("#discountAmount").val(discount);
        return false;
      }
      totalTax += elements[i].value * taxEle[i].value / 100;
    } else {
      amount += parseFloat(elements[i].value);
    }
  }
  discount = discount == '' ? 0 : discount;
  netAmount = parseFloat(total) + parseFloat(totalTax);
  netAmount = parseFloat(netAmount) - parseFloat(discount);
  if (discount > total && $(this).hasClass('purchase-discount')) {
    discount = discount.slice(0, -1);
    displayErrorMessage(Lang.get("js.the_discount_shoul"));
    $("#discountAmount").val(discount);
    return false;
  }
  if (discount > total) {
    netAmount = 0;
  }
  $("#total").val(total.toFixed(2));
  $("#purchaseTaxId").val(totalTax.toFixed(2));
  $("#netAmount").val(netAmount.toFixed(2));
  // let value = $(this).val();
  // $(this).val(value.replace(/[^0-9\.]/g, ""));
  // var currentRow = $(this).closest("tr");
  // let currentqty = currentRow.find(".purchase-quantity").val();
  // let price = currentRow.find(".purchase-price").val();
  // let medicineBillTax = currentRow.find(".purchase-tax").val();
  // let currentamount = parseFloat(price * currentqty);
  // currentRow.find(".amount").val(currentamount.toFixed(2));

  // let y = $(".purchaseMedicineId").length;
  // let taxEle = $(".purchase-tax");
  // let elements = $(".amount");
  // let total = 0.0;
  // let totalTax = 0;
  // let netAmount = 0;
  // let discount = 0;
  // let amount = 0;
  // var qty = $(".purchase-quantity");

  // for (let i = 0; i < elements.length; i++) {
  //     total += parseFloat(elements[i].value);
  //     discount = $(".purchase-discount").val();
  //     let taxAmount = $(this).val();
  //     if (taxEle[i].value != 0 && taxEle[i].value != "") {
  //         if (taxEle[i].value > 99) {
  //             let taxAmount = taxEle[i].value.slice(0, -1);
  //             currentRow.find(".purchase-tax").val(taxAmount);
  //             displayErrorMessage(
  //                 Lang.get("Taxes should be less than 100%.")
  //             );
  //             $("#discountAmount").val(discount);
  //             return false;
  //         }
  //         totalTax += (elements[i].value * taxEle[i].value) / 100;
  //         amount += parseFloat(elements[i].value) + parseFloat(totalTax);
  //     } else {
  //         amount += parseFloat(elements[i].value);
  //     }
  // }
  // discount = discount == "" ? 0 : discount;
  // netAmount = parseFloat(amount) - parseFloat(discount);
  // if (discount > total && $(this).hasClass("purchase-discount")) {
  //     discount = discount.slice(0, -1);
  //     displayErrorMessage(
  //         Lang.get("The discount should be less than the total amount.")
  //     );
  //     $("#discountAmount").val(discount);
  //     return false;
  // }
  // if (discount > total) {
  //     netAmount = 0;
  // }
  // $("#total").val(total.toFixed(2));
  // $("#purchaseTaxId").val(totalTax.toFixed(2));
  // $("#netAmount").val(netAmount.toFixed(2));
});
listenClick(".delete-purchase-medicine-item", function () {
  var currentRow = $(this).closest("tr");
  var currentRowAmount = currentRow.find('.purchase-amount').val();
  var currentRowTax = currentRow.find('.purchase-tax').val();
  var currentTaxAmount = parseFloat(currentRowAmount) * parseFloat(currentRowTax / 100);
  var updatedTax = parseFloat($('#purchaseTaxId').val()) - parseFloat(currentTaxAmount);
  $('#purchaseTaxId').val(updatedTax.toFixed(2));
  var updatedTotalAmount = parseFloat($('#total').val()) - parseFloat(currentRowAmount);
  $('#total').val(updatedTotalAmount.toFixed(2));
  var amountSubfromNetAmt = parseFloat(currentTaxAmount) + parseFloat(currentRowAmount);
  var updateNetAmount = parseFloat($('#netAmount').val()) - parseFloat(amountSubfromNetAmt);
  $('#netAmount').val(updateNetAmount.toFixed(2));
  $(this).parents("tr").remove();
});
listenSubmit("#purchaseMedicineFormId", function (e) {
  e.preventDefault();
  var y = $("#purchaseUniqueId").val() - 1;
  var tx = 1;
  for (var i = 1; i <= y; i++) {
    var medicinID = "#medicineChooseId" + i;
    var taxId = "tax" + i;
    if (typeof $(taxId).val() != "undefined") {
      if ($(taxId).val() == null || $(taxId).val() == "") {
        tx = 0;
      }
    }
    if (typeof $(medicinID).val() != "undefined") {
      if ($(medicinID).val() == null || $(medicinID).val() == "") {
        displayErrorMessage(Lang.get('js.enter_lot_number'));
        return false;
      }
    }
    var lotNum = "#lot_no" + i;
    if (typeof $(lotNum).val() != "undefined") {
      if ($(lotNum).val() == null || $(lotNum).val() == "") {
        displayErrorMessage(Lang.get('js.enter_lot_number'));
        return false;
      }
    }
    var salePrice = "#sale_price" + i;
    if (typeof $(salePrice).val() != "undefined") {
      if ($(salePrice).val() == null || $(salePrice).val() == "") {
        displayErrorMessage(Lang.get('js.enter_sale_price'));
        return false;
      }
    }
    var purchasePrice = "#purchase_price" + i;
    if (typeof $(purchasePrice).val() != "undefined") {
      if ($(purchasePrice).val() == null || $(purchasePrice).val() == "") {
        displayErrorMessage("Enter purchase price.");
        return false;
      } else if ($(purchasePrice).val() == 0) {
        displayErrorMessage(Lang.get('js.quantity_should'));
        return false;
      }
    }
    var quantityID = "#quantity" + i;
    if (typeof $(quantityID).val() != "undefined") {
      if ($(quantityID).val() == null || $(quantityID).val() == "") {
        displayErrorMessage("Enter quantity.");
        return false;
      } else if ($(quantityID).val() == 0) {
        displayErrorMessage(Lang.get('js.quantity_should'));
        return false;
      }
    }
  }
  var netAmount = "#netAmount";
  if ($(netAmount).val() == null || $(netAmount).val() == "") {
    displayErrorMessage(Lang.get("js.net_amount_not_empty"));
    return false;
  } else if ($(netAmount).val() == 0) {
    displayErrorMessage(Lang.get("js.net_amount_not_zero"));
    return false;
  }
  if (tx == 0 && ($("#purchaseTaxId").val() == null || $("#purchaseTaxId").val() == "")) {
    displayErrorMessage(Lang.get("js.tax_cannot_be_zero_empty"));
    return false;
  }
  $(this)[0].submit();
});
listenClick(".purchaseMedicineDelete", function (event) {
  var id = $(event.currentTarget).attr("data-id");
  deleteItem(route("medicine-purchase.destroy", id), Lang.get("js.purchase_medicine"));
});

/***/ }),

/***/ "./resources/assets/js/reviews/review.js":
/*!***********************************************!*\
  !*** ./resources/assets/js/reviews/review.js ***!
  \***********************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadReviewData);
function loadReviewData() {
  var star_rating_width = $('.fill-ratings span').width();
  $('.star-ratings').width(star_rating_width);
}
listenClick('.addReviewBtn', function () {
  var reviewDoctorId = $(this).attr('data-id');
  $('#reviewDoctorId').val(reviewDoctorId);
});
listenSubmit('#addReviewForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('patients.reviews.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#addReviewModal').modal('hide');
        setTimeout(function () {
          location.reload();
        }, 1200);
      }
    },
    error: function error(_error) {
      displayErrorMessage(_error.responseJSON.message);
    }
  });
});
listenClick('.editReviewBtn', function () {
  var reviewId = $(this).attr('data-id');
  $.ajax({
    url: route('patients.reviews.edit', reviewId),
    type: 'GET',
    success: function success(result) {
      $('#editReviewModal').modal('show').appendTo('body');
      $('#editDoctorId').val(result.data.doctor_id);
      $('#editReviewId').val(result.data.id);
      $('#editReview').val(result.data.review);
      $('#editRating-' + result.data.rating).attr('checked', true);
    },
    error: function error(_error2) {
      displayErrorMessage(_error2.responseJSON.message);
    }
  });
});
listenSubmit('#editReviewForm', function (e) {
  e.preventDefault();
  var reviewId = $('#editReviewId').val();
  $.ajax({
    url: route('patients.reviews.update', reviewId),
    type: 'PUT',
    data: $(this).serialize(),
    success: function success(result) {
      displaySuccessMessage(result.message);
      $('#editReviewModal').modal('hide');
      setTimeout(function () {
        location.reload();
      }, 1200);
    },
    error: function error(_error3) {
      displayErrorMessage(_error3.responseJSON.message);
    }
  });
});
listenClick('.addReviewBtn', function () {
  $('#addReviewModal').modal('show').appendTo('body');
});
listen('hidden.bs.modal', '#addReviewModal', function () {
  $('#reviewDoctorId').val('');
  resetModalForm('#addReviewForm');
});
listen('hidden.bs.modal', '#editReviewModal', function () {
  $('#editDoctorId').val('');
  resetModalForm('#editReviewForm');
});

/***/ }),

/***/ "./resources/assets/js/roles/create-edit.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/roles/create-edit.js ***!
  \**************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadRoleData);
function loadRoleData() {
  var totalPermissionsCount = parseInt($('#totalPermissions').val() - 1);
  var checkAllLength = $('.permission:checked').length;
  var roleIsEdit = $('#roleIsEdit').val();
  if (roleIsEdit == true) {
    if (checkAllLength === totalPermissionsCount) {
      $('#checkAllPermission').prop('checked', true);
    } else {
      $('#checkAllPermission').prop('checked', false);
    }
  }
}
listenClick('#checkAllPermission', function () {
  if ($('#checkAllPermission').is(':checked')) {
    $('.permission').each(function () {
      $(this).prop('checked', true);
    });
  } else {
    $('.permission').each(function () {
      $(this).prop('checked', false);
    });
  }
});
listenClick('.permission', function () {
  var checkAllLength = $('.permission:checked').length;
  var totalPermissionsCount = parseInt($('#totalPermissions').val() - 1);
  if (checkAllLength === totalPermissionsCount) {
    $('#checkAllPermission').prop('checked', true);
  } else {
    $('#checkAllPermission').prop('checked', false);
  }
});

/***/ }),

/***/ "./resources/assets/js/roles/roles.js":
/*!********************************************!*\
  !*** ./resources/assets/js/roles/roles.js ***!
  \********************************************/
/***/ (() => {

listenClick('.role-delete-btn', function (event) {
  var roleRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('roles.destroy', roleRecordId), Lang.get('js.roles'));
});

/***/ }),

/***/ "./resources/assets/js/service_categories/service_categories.js":
/*!**********************************************************************!*\
  !*** ./resources/assets/js/service_categories/service_categories.js ***!
  \**********************************************************************/
/***/ (() => {

listenClick('#createServiceCategory', function () {
  $('#createServiceCategoryPageModal').modal('show').appendTo('body');
});
listen('hidden.bs.modal', '#createServiceCategoryPageModal', function () {
  resetModalForm('#createServiceCategoryForm', '#createServiceCategoryValidationErrorsBox');
});
listen('hidden.bs.modal', '#editServiceCategoryModal', function () {
  resetModalForm('#editServiceCategoryForm', '#editServiceCategoryValidationErrorsBox');
});
listenClick('.service-category-edit-btn', function (event) {
  var editServiceCategoryId = $(event.currentTarget).attr('data-id');
  renderData(editServiceCategoryId);
});
function renderData(id) {
  $.ajax({
    url: route('service-categories.edit', id),
    type: 'GET',
    success: function success(result) {
      $('#serviceCategoryID').val(result.data.id);
      $('#editServiceCategoryName').val(result.data.name);
      $('#editServiceCategoryModal').modal('show');
    }
  });
}
listenSubmit('#createServiceCategoryForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('service-categories.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        Livewire.dispatch('refresh');
        $('#createServiceCategoryPageModal').modal('hide');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit('#editServiceCategoryForm', function (e) {
  e.preventDefault();
  var updateServiceCategoryId = $('#serviceCategoryID').val();
  $.ajax({
    url: route('service-categories.update', updateServiceCategoryId),
    type: 'PUT',
    data: $(this).serialize(),
    success: function success(result) {
      $('#editServiceCategoryModal').modal('hide');
      displaySuccessMessage(result.message);
      Livewire.dispatch('refresh');
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('.service-category-delete-btn', function (event) {
  var serviceCategoryRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('service-categories.destroy', serviceCategoryRecordId), Lang.get('js.service_category'));
});

/***/ }),

/***/ "./resources/assets/js/services/create-edit.js":
/*!*****************************************************!*\
  !*** ./resources/assets/js/services/create-edit.js ***!
  \*****************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadServiceData);
function loadServiceData() {
  if (!$('.price-input').length) {
    return;
  }
  var price = $('.price-input').val();
  if (price === '') {
    $('.price-input').val('');
  } else {
    if (/[0-9]+(,[0-9]+)*$/.test(price)) {
      $('.price-input').val(getFormattedPrice(price));
      return true;
    } else {
      $('.price-input').val(price.replace(/[^0-9 \,]/, ''));
    }
  }
}
listenClick('#createServiceCategory', function () {
  $('#serviceCreateServiceCategoryModal').modal('show').appendTo('body');
});
listenSubmit('#serviceCreateServiceCategoryForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('service-categories.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#serviceCreateServiceCategoryModal').modal('hide');
        var data = {
          id: result.data.id,
          name: result.data.name
        };
        var newOption = new Option(data.name, data.id, false, true);
        $('#serviceCategory').append(newOption).trigger('change');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      processingBtn('#serviceCreateServiceCategoryForm', '#btnSave');
    }
  });
});
listen('hidden.bs.modal', '#serviceCreateServiceCategoryModal', function () {
  resetModalForm('#serviceCreateServiceCategoryForm', '#createServiceCategoryValidationErrorsBox');
});

/***/ }),

/***/ "./resources/assets/js/services/services.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/services/services.js ***!
  \**************************************************/
/***/ (() => {

listenClick('#serviceResetFilter', function () {
  $('#servicesStatus').val($('#allServices').val()).trigger('change');
});
listenChange('#servicesStatus', function () {
  Livewire.dispatch('changeStatusFilter', $(this).val());
});
listenClick('.service-delete-btn', function (event) {
  var serviceRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('services.destroy', serviceRecordId), Lang.get('js.service'));
});
listenClick('.service-statusbar', function (event) {
  var recordId = $(event.currentTarget).attr('data-id');
  $.ajax({
    type: 'PUT',
    url: route('service.status'),
    data: {
      id: recordId
    },
    success: function success(result) {
      displaySuccessMessage(result.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/settings/settings.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/settings/settings.js ***!
  \**************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', loadSettingData);
var form;
var phone;
var prefixCode;
var loadData = false;
function loadSettingData() {
  var settingCountryId = $('#settingCountryId').val();
  var settingStateId = $('#settingStateId').val();
  var settingCityId = $('#settingCityId').val();
  if (settingCountryId != '') {
    $('#settingCountryId').val(settingCountryId).trigger('change');
    setTimeout(function () {
      $('#settingStateId').val(settingStateId).trigger('change');
    }, 800);
    setTimeout(function () {
      $('#settingCityId').val(settingCityId).trigger('change');
    }, 400);
    loadData = true;
  }
  if (!$('#generalSettingForm').length) {
    return;
  }
  form = document.getElementById('generalSettingForm');
  phone = document.getElementById('phoneNumber').value;
  prefixCode = document.getElementById('prefix_code').value;
  var input = document.querySelector('#defaultCountryData');
  var intl = window.intlTelInput(input, {
    initialCountry: defaultCountryCodeValue,
    separateDialCode: true,
    geoIpLookup: function geoIpLookup(success, failure) {
      $.get('https://ipinfo.io', function () {}, 'jsonp').always(function (resp) {
        var countryCode = resp && resp.country ? resp.country : '';
        success(countryCode);
      });
    },
    utilsScript: '../../public/assets/js/inttel/js/utils.min.js'
  });
  var getCode = intl.selectedCountryData['name'] + ' +' + intl.selectedCountryData['dialCode'];
  $('#defaultCountryData').val(getCode);
}
listenKeyup('#defaultCountryData', function () {
  var str2 = $(this).val().slice(0, -1) + '';
  return $(this).val(str2);
});
listenClick('.iti__standard', function () {
  var currentSelectedFlag = $(this).parent().parent().parent().next();
  $(this).attr('data-country-code');
  if (currentSelectedFlag.has('#defaultCountryCode')) {
    $('#defaultCountryCode').val($(this).attr('data-country-code'));
  }
  var CountryDataVal = $(this).children('.iti__country-name').text() + ' ' + $(this).children('.iti__dial-code').text();
  $('#defaultCountryData').val(CountryDataVal);
});
listenChange('#settingCountryId', function () {
  $.ajax({
    url: route('states-list'),
    type: 'get',
    dataType: 'json',
    data: {
      settingCountryId: $(this).val()
    },
    success: function success(data) {
      $('#settingStateId').empty();
      $('#settingCityId').empty();
      $('#settingStateId').append($('<option value=""></option>').text(Lang.get('js.select_state')));
      $('#settingCityId').append($('<option value=""></option>').text(Lang.get('js.select_city')));
      $.each(data.data.states, function (i, v) {
        $('#settingStateId').append($("<option ".concat(!loadData && i == data.data.state_id ? 'selected' : '', "></option>")).attr('value', i).text(v));
      });
    }
  });
});
listenChange('#settingrecaptcha', function () {
  var settingrechapcha = $('#settingrecaptcha').prop("checked");
  if (settingrechapcha == false) {
    $('.recaptcha-field').css('display', 'none');
  } else {
    $('.recaptcha-field').css('display', 'block');
  }
});
listenChange('#settingStateId', function () {
  $('#settingCityId').empty();
  $.ajax({
    url: route('cities-list'),
    type: 'get',
    dataType: 'json',
    data: {
      stateId: $(this).val()
    },
    success: function success(data) {
      $('#settingCityId').empty();
      $('#settingCityId').append($('<option value=""></option>').text(Lang.get('js.select_city')));
      $.each(data.data.cities, function (i, v) {
        $('#settingCityId').append($("<option ".concat(loadData && i == data.data.city_id ? 'selected' : '', "></option>")).attr('value', i).text(v));
      });
    }
  });
});
listenClick('#settingSubmitBtn', function () {
  var checkedPaymentMethod = $('input[name="payment_gateway[]"]:checked').length;
  if (!checkedPaymentMethod) {
    displayErrorMessage(Lang.get('js.select_payment'));
    return false;
  }
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
  $("#generalSettingForm")[0].submit();
});

/***/ }),

/***/ "./resources/assets/js/specializations/specializations.js":
/*!****************************************************************!*\
  !*** ./resources/assets/js/specializations/specializations.js ***!
  \****************************************************************/
/***/ (() => {

listenClick('#createSpecialization', function () {
  $('#createSpecializationModal').modal('show').appendTo('body');
});
listen('hidden.bs.modal', '#createSpecializationModal', function () {
  resetModalForm('#createSpecializationForm', '#createSpecializationValidationErrorsBox');
});
listen('hidden.bs.modal', '#editSpecializationModal', function () {
  resetModalForm('#editSpecializationForm', '#editSpecializationValidationErrorsBox');
});
listenClick('.specialization-edit-btn', function (event) {
  var editSpecializationId = $(event.currentTarget).attr('data-id');
  renderData(editSpecializationId);
});
function renderData(id) {
  $.ajax({
    url: route('specializations.edit', id),
    type: 'GET',
    success: function success(result) {
      $('#specializationID').val(result.data.id);
      $('#editName').val(result.data.name);
      $('#editSpecializationModal').modal('show');
    }
  });
}
listenSubmit('#createSpecializationForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('specializations.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#createSpecializationModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit('#editSpecializationForm', function (e) {
  e.preventDefault();
  var updateSpecializationId = $('#specializationID').val();
  $.ajax({
    url: route('specializations.update', updateSpecializationId),
    type: 'PUT',
    data: $(this).serialize(),
    success: function success(result) {
      $('#editSpecializationModal').modal('hide');
      displaySuccessMessage(result.message);
      Livewire.dispatch('refresh');
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('.specialization-delete-btn', function (event) {
  var specializationRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('specializations.destroy', specializationRecordId), Lang.get('js.specializations'));
});

/***/ }),

/***/ "./resources/assets/js/staff/create-edit.js":
/*!**************************************************!*\
  !*** ./resources/assets/js/staff/create-edit.js ***!
  \**************************************************/
/***/ (() => {

listenChange('input[type=radio][name=gender]', function () {
  var file = $('#profilePicture').val();
  if (isEmpty(file)) {
    if (this.value == 1) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + manAvatar + ')');
    } else if (this.value == 2) {
      $('.image-input-wrapper').attr('style', 'background-image:url(' + womanAvatar + ')');
    }
  }
});
listenSubmit('#createStaffForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenSubmit('#editStaffForm', function () {
  if ($('#error-msg').text() !== '') {
    $('#phoneNumber').focus();
    displayErrorMessage(Lang.get('js.contact_number') + $('#error-msg').text());
    return false;
  }
});
listenClick('.removeAvatarIcon', function () {
  $('#bgImage').css('background-image', '');
  $('#bgImage').css('background-image', 'url(' + backgroundImg + ')');
  $('#removeAvatar').addClass('hide');
  $('#tooltip287851').addClass('hide');
});

/***/ }),

/***/ "./resources/assets/js/staff/staff.js":
/*!********************************************!*\
  !*** ./resources/assets/js/staff/staff.js ***!
  \********************************************/
/***/ (() => {

listenClick('.staff-delete-btn', function (event) {
  var staffRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('staffs.destroy', staffRecordId), Lang.get('js.staff'));
});
listenChange('.staff-email-verified', function (e) {
  var verifyRecordId = $(e.currentTarget).attr('data-id');
  var value = $(this).is(':checked') ? 1 : 0;
  $.ajax({
    type: 'POST',
    url: route('emailVerified'),
    data: {
      id: verifyRecordId,
      value: value
    },
    success: function success(result) {
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    }
  });
});
listenClick('.staff-email-verification', function (event) {
  var staffVerifyId = $(event.currentTarget).attr('data-id');
  $.ajax({
    type: 'POST',
    url: route('resend.email.verification', staffVerifyId),
    success: function success(result) {
      Livewire.dispatch('refresh');
      displaySuccessMessage(result.message);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/states/states.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/states/states.js ***!
  \**********************************************/
/***/ (() => {

listenClick('#addState', function () {
  $('#addStateModal').modal('show').appendTo('body');
  $('#countryState').select2({
    dropdownParent: $('#addStateModal')
  });
});
listenSubmit('#addStateForm', function (e) {
  e.preventDefault();
  $.ajax({
    url: route('states.store'),
    type: 'POST',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#addStateModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('.state-edit-btn', function (event) {
  $('#editStateModal').modal('show').appendTo('body');
  $('#selectCountry').select2({
    dropdownParent: $('#editStateModal')
  });
  var editStateId = $(event.currentTarget).attr('data-id');
  $('#editStateId').val(editStateId);
  $.ajax({
    url: route('states.edit', editStateId),
    type: 'GET',
    success: function success(result) {
      if (result.success) {
        $('#editStateName').val(result.data.name);
        $('#selectCountry').val(result.data.country_id).trigger('change');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenSubmit('#editStateForm', function (event) {
  event.preventDefault();
  var updateStateId = $('#editStateId').val();
  $.ajax({
    url: route('states.update', updateStateId),
    type: 'PUT',
    data: $(this).serialize(),
    success: function success(result) {
      if (result.success) {
        displaySuccessMessage(result.message);
        $('#editStateModal').modal('hide');
        Livewire.dispatch('refresh');
      }
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listen('hidden.bs.modal', '#addStateModal', function (e) {
  $('#addStateForm')[0].reset();
  $('#countryState').val(null).trigger('change');
});
listenClick('.state-delete-btn', function (event) {
  var stateRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('states.destroy', stateRecordId), Lang.get('js.state'));
});

/***/ }),

/***/ "./resources/assets/js/transactions/patient-transactions.js":
/*!******************************************************************!*\
  !*** ./resources/assets/js/transactions/patient-transactions.js ***!
  \******************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
document.addEventListener('DOMContentLoaded', loadPatientTransactionTable);
var patientTransactionTableName = '#patientTransactionsTable';
function loadPatientTransactionTable() {
  if (!$(patientTransactionTableName).length) {
    return;
  }
  var patientTransactionTbl = $(patientTransactionTableName).DataTable({
    processing: true,
    serverSide: true,
    searchDelay: 500,
    'language': {
      'lengthMenu': 'Show _MENU_'
    },
    'order': [[0, 'desc']],
    ajax: {
      url: route('patients.transactions')
    },
    columnDefs: [{
      'targets': [0],
      'width': '50%'
    }, {
      'targets': [1],
      'width': '18%'
    }, {
      'targets': [3],
      'orderable': false,
      'searchable': false,
      'className': 'text-center',
      'width': '8%'
    }],
    columns: [{
      data: function data(row) {
        return "<span class=\"badge badge-light-info\">".concat(moment.parseZone(row.created_at).format('Do MMM, Y h:mm A'), "</span>");
      },
      name: 'created_at'
    }, {
      data: function data(row) {
        if (row.type == manuallyMethod) {
          return manually;
        }
        if (row.type == stripeMethod) {
          return stripe;
        }
        if (row.type == paystckMethod) {
          return paystck;
        }
        if (row.type == paypalMethod) {
          return paypal;
        }
        if (row.type == authorizeMethod) {
          return authorize;
        }
        if (row.type == paytmMethod) {
          return paytm;
        }
        return '';
      },
      name: 'type'
    }, {
      data: function data(row) {
        return currencyIcon + ' ' + getFormattedPrice(row.amount);
      },
      name: 'amount'
    }, {
      data: function data(row) {
        var patientTransactionData = [{
          'id': row.id,
          'showUrl': route('patients.transactions.show', row.id)
        }];
        return prepareTemplateRender('#transactionsTemplate', patientTransactionData);
      },
      name: 'id'
    }]
  });
  handleSearchDatatable(patientTransactionTbl);
  document.addEventListener('DOMContentLoaded', loadTransactionFilterDate);
  function loadTransactionFilterDate() {
    if (!$('#transactionDateFilter').length) {
      return;
    }
    var appointmentStart = moment().startOf('week');
    var appointmentEnd = moment().endOf('week');
    function cb(start, end) {
      $('#transactionDateFilter').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    }
    var transactionDatePicker = $('#transactionDateFilter').daterangepicker({
      startDate: appointmentStart,
      endDate: appointmentEnd,
      opens: 'left',
      showDropdowns: true,
      locale: {
        customRangeLabel: Lang.get('js.custom'),
        applyLabel: Lang.get('js.apply'),
        cancelLabel: Lang.get('js.cancel'),
        fromLabel: Lang.get('js.from'),
        toLabel: Lang.get('js.to'),
        monthNames: [Lang.get('js.jan'), Lang.get('js.feb'), Lang.get('js.mar'), Lang.get('js.apr'), Lang.get('js.may'), Lang.get('js.jun'), Lang.get('js.jul'), Lang.get('js.aug'), Lang.get('js.sep'), Lang.get('js.oct'), Lang.get('js.nov'), Lang.get('js.dec')],
        daysOfWeek: [Lang.get('js.sun'), Lang.get('js.mon'), Lang.get('js.tue'), Lang.get('js.wed'), Lang.get('js.thu'), Lang.get('js.fri'), Lang.get('js.sat')]
      },
      ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get('js.today'), [moment(), moment()]), Lang.get('js.yesterday'), [moment().subtract(1, 'days'), moment().subtract(1, 'days')]), Lang.get('js.this_week'), [moment().startOf('week'), moment().endOf('week')]), Lang.get('js.last_30_days'), [moment().subtract(29, 'days'), moment()]), Lang.get('js.this_month'), [moment().startOf('month'), moment().endOf('month')]), Lang.get('js.last_month'), [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')])
    }, cb);
    cb(appointmentStart, appointmentEnd);
    transactionDatePicker.on("apply.daterangepicker", function (ev, picker) {
      Livewire.dispatch("changeDateFilter", {
        date: $(this).val()
      });
    });
  }
}
listenClick('.transaction-statusbar', function (event) {
  var recordId = $(event.currentTarget).attr('data-id');
  var acceptPaymentUserId = currentLoginUserId;
  $.ajax({
    type: 'PUT',
    url: route('transaction.status'),
    data: {
      id: recordId,
      acceptPaymentUserId: acceptPaymentUserId
    },
    success: function success(result) {
      if (result.success) {
        Livewire.dispatch('refresh');
        displaySuccessMessage(Lang.get('js.status_update'));
      }
    },
    error: function error(result) {
      Livewire.dispatch('refresh');
      displayErrorMessage(result.responseJSON.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/transactions/transactions.js":
/*!**********************************************************!*\
  !*** ./resources/assets/js/transactions/transactions.js ***!
  \**********************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// document.addEventListener('DOMContentLoaded', loadTransactionFilterDate)

var appointmentStart = moment().startOf("week");
var appointmentEnd = moment().endOf("week");
Livewire.hook("element.init", function () {
  loadTransactionFilterDate();
  if ($("#trPaymentMehtod").length) {
    $("#trPaymentMehtod").select2();
  }
  if ($("#transactionStatus").length) {
    $("#transactionStatus").select2();
  }
  if ($("#transactionDoctor").length) {
    $("#transactionDoctor").select2();
  }
  if ($("#transactionServices").length) {
    $("#transactionServices").select2();
  }
  if (appointmentStart != undefined && appointmentEnd != undefined) {
    cb(appointmentStart, appointmentEnd);
  }
});
function loadTransactionFilterDate() {
  if (!$("#transactionDateFilter").length) {
    return;
  }

  // let appointmentStart = moment().startOf('week')
  // let appointmentEnd = moment().endOf('week')

  var transactionDatePicker = $("#transactionDateFilter").daterangepicker({
    startDate: appointmentStart,
    endDate: appointmentEnd,
    opens: "left",
    showDropdowns: true,
    locale: {
      customRangeLabel: Lang.get("js.custom"),
      applyLabel: Lang.get("js.apply"),
      cancelLabel: Lang.get("js.cancel"),
      fromLabel: Lang.get("js.from"),
      toLabel: Lang.get("js.to"),
      monthNames: [Lang.get("js.jan"), Lang.get("js.feb"), Lang.get("js.mar"), Lang.get("js.apr"), Lang.get("js.may"), Lang.get("js.jun"), Lang.get("js.jul"), Lang.get("js.aug"), Lang.get("js.sep"), Lang.get("js.oct"), Lang.get("js.nov"), Lang.get("js.dec")],
      daysOfWeek: [Lang.get("js.sun"), Lang.get("js.mon"), Lang.get("js.tue"), Lang.get("js.wed"), Lang.get("js.thu"), Lang.get("js.fri"), Lang.get("js.sat")]
    },
    ranges: _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, Lang.get("js.today"), [moment(), moment()]), Lang.get("js.yesterday"), [moment().subtract(1, "days"), moment().subtract(1, "days")]), Lang.get("js.this_week"), [moment().startOf("week"), moment().endOf("week")]), Lang.get("js.last_30_days"), [moment().subtract(29, "days"), moment()]), Lang.get("js.this_month"), [moment().startOf("month"), moment().endOf("month")]), Lang.get("js.last_month"), [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")])
  }
  // cb
  );

  //cb(appointmentStart, appointmentEnd);

  transactionDatePicker.on("apply.daterangepicker", function (ev, picker) {
    var date = picker.startDate.format("DD/MM/YYYY") + " - " + picker.endDate.format("DD/MM/YYYY");
    Livewire.dispatch("changeDateFilter", {
      date: date
    });
    appointmentStart = picker.startDate;
    appointmentEnd = picker.endDate;
  });
  window.addEventListener("update-item", function (event) {
    var array = event.detail.data;
    console.log(array);
    $("#transactionDoctor").empty();
    $("#transactionDoctor").append($('<option value=""></option>').text(Lang.get("js.select_doctor")));
    $.each(array, function (key, value) {
      $("#transactionDoctor").append($("<option></option>").attr("value", key).text(value));
    });
  });
}
function cb(start, end) {
  $("#transactionDateFilter").val(start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"));
}
listenChange("#trPaymentMehtod", function () {
  Livewire.dispatch("paymentFilter", {
    pType: $(this).val()
  });
});
listenChange("#transactionStatus", function () {
  Livewire.dispatch("statusFilter", {
    statusType: $(this).val()
  });
});
listenChange("#transactionDoctor", function () {
  Livewire.dispatch("doctorFilter", {
    doctorType: $(this).val()
  });
});
listenChange("#transactionServices", function () {
  Livewire.dispatch("serviceFilter", {
    serviceType: $(this).val()
  });
});
listenClick("#transactionResetFilter", function () {
  $("#trPaymentMehtod").val("").trigger("change");
  $("#transactionStatus").val("").trigger("change");
  $("#transactionDoctor,#transactionServices").val("").trigger("change");
  hideDropdownManually($("#transactionFilterBtn"), $(".dropdown-menu"));
});

/***/ }),

/***/ "./resources/assets/js/users/user-profile.js":
/*!***************************************************!*\
  !*** ./resources/assets/js/users/user-profile.js ***!
  \***************************************************/
/***/ (() => {

listenClick('#changePassword', function () {
  $('#changePasswordForm')[0].reset();
  $('.pass-check-meter div.flex-grow-1').removeClass('active');
  $('#changePasswordModal').modal('show').appendTo('body');
});
listenClick('#changeLanguage', function () {
  $('#changeLanguageModal').modal('show').appendTo('body');
});
listenClick('#passwordChangeBtn', function () {
  $.ajax({
    url: changePasswordUrl,
    type: 'PUT',
    data: $('#changePasswordForm').serialize(),
    success: function success(result) {
      $('#changePasswordModal').modal('hide');
      $('#changePasswordForm')[0].reset();
      displaySuccessMessage(result.message);
      setTimeout(function () {
        location.reload();
      }, 1000);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
window.printErrorMessage = function (selector, errorResult) {
  $(selector).show().html('');
  $(selector).text(errorResult.message);
};
listenClick('#emailNotification', function () {
  $('#emailNotificationModal').modal('show').appendTo('body');
  if ($('#emailNotificationForm').length) {
    $('#emailNotificationForm')[0].reset();
  }
});
listenClick('#emailNotificationChange', function () {
  $.ajax({
    url: route('emailNotification'),
    type: 'PUT',
    data: $('#emailNotificationForm').serialize(),
    success: function success(result) {
      $('#emailNotificationModal').modal('hide');
      displaySuccessMessage(result.message);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});
listenClick('#languageChangeBtn', function () {
  $.ajax({
    url: updateLanguageURL,
    type: 'POST',
    data: $('#changeLanguageForm').serialize(),
    success: function success(result) {
      $('#changeLanguageModal').modal('hide');
      displaySuccessMessage(result.message);
      window.location.reload();
      // Turbo.visit(window.location.href);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    }
  });
});

/***/ }),

/***/ "./resources/assets/js/visits/create-edit.js":
/*!***************************************************!*\
  !*** ./resources/assets/js/visits/create-edit.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flatpickr/dist/l10n */ "./node_modules/flatpickr/dist/l10n/index.js");
/* harmony import */ var flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flatpickr_dist_l10n__WEBPACK_IMPORTED_MODULE_0__);

document.addEventListener('DOMContentLoaded', loadVisitData);
function loadVisitData() {
  var visitDate = '.visit-date';
  if (!$(visitDate).length) {
    return;
  }
  var lang = $('.currentLanguage').val();
  $(visitDate).flatpickr({
    "locale": lang,
    disableMobile: true
  });
}
listenSubmit('#saveForm', function (e) {
  e.preventDefault();
  $('#btnSubmit').attr('disabled', true);
  $('#saveForm')[0].submit();
});

/***/ }),

/***/ "./resources/assets/js/visits/doctor-visit.js":
/*!****************************************************!*\
  !*** ./resources/assets/js/visits/doctor-visit.js ***!
  \****************************************************/
/***/ (() => {

listenClick('.doctor-visit-delete-btn', function (event) {
  var visitDoctorRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('doctors.visits.destroy', visitDoctorRecordId), Lang.get('js.visits'));
});

/***/ }),

/***/ "./resources/assets/js/visits/show-page.js":
/*!*************************************************!*\
  !*** ./resources/assets/js/visits/show-page.js ***!
  \*************************************************/
/***/ (() => {

setTimeout(function () {
  $('.visit-detail-width').parent().parent().addClass('visit-detail-width');
}, 100);

// Add visit Problem Data
listenSubmit('#addVisitProblem', function (e) {
  e.preventDefault();
  var problemName = $('#problemName').val();
  var empty = problemName.trim().replace(/ \r\n\t/g, '') === '';
  if (empty) {
    displayErrorMessage(Lang.get('js.problem_white_space'));
    return false;
  }
  var btnSubmitEle = $(this).find('#problemSubmitBtn');
  setAdminBtnLoader(btnSubmitEle);
  var problemAddUrl = $('#doctorLogin').val() ? route('doctors.visits.add.problem') : route('add.problem');
  $.ajax({
    url: problemAddUrl,
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function success(result) {
      $('ul#problemLists').empty();
      if (result.data.length > 0) {
        displaySuccessMessage(result.message);
        $.each(result.data, function (i, val) {
          $('#problemName').val('');
          $('#problemLists').append("<li class=\"list-group-item text-break text-wrap d-flex justify-content-between align-items-center py-5\">".concat(val.problem_name, "<span class=\"remove-problem\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"Delete\" data-id=\"").concat(val.id, "\"><a href=\"javascript:void(0)\"><i class=\"fas fa-trash text-danger\"></i></a></span></li>"));
        });
      } else {
        $('#problemLists').append("<p class=\"text-center fw-bold text-muted mt-3\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
      }
    },
    complete: function complete() {
      $('#problemSubmitBtn').attr('disabled', false);
    }
  });
});

// Delete Visit Problem Data
listenClick('.remove-problem', function (e) {
  e.preventDefault();
  var id = $(this).attr('data-id');
  var problemDeleteUrl = $('#doctorLogin').val() ? route('doctors.visits.delete.problem', id) : route('delete.problem', id);
  $(this).closest('li').remove();
  $.ajax({
    url: problemDeleteUrl,
    type: 'POST',
    dataType: 'json',
    success: function success(result) {
      if (result.success) {
        if ($('#problemLists li').length < 1) {
          displaySuccessMessage(result.message);
          $('#problemLists').append("<p class=\"text-center fw-bold mt-3 text-muted text-gray-600\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
        } else {
          displaySuccessMessage(result.message);
        }
      }
    }
  });
});

// Add Visit Observation Data
listenSubmit('#addVisitObservation', function (e) {
  e.preventDefault();
  var observationName = $('#observationName').val();
  var empty2 = observationName.trim().replace(/ \r\n\t/g, '') === '';
  if (empty2) {
    displayErrorMessage(Lang.get('js.observation_white_space'));
    return false;
  }
  var btnSubmitEle = $(this).find('#observationSubmitBtn');
  setAdminBtnLoader(btnSubmitEle);
  var observationAddUrl = $('#doctorLogin').val() ? route('doctors.visits.add.observation') : route('add.observation');
  $.ajax({
    url: observationAddUrl,
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function success(result) {
      $('ul#observationLists').empty();
      if (result.data.length > 0) {
        displaySuccessMessage(result.message);
        $.each(result.data, function (i, val) {
          $('#observationName').val('');
          $('#observationLists').append("<li class=\"list-group-item text-break text-wrap d-flex justify-content-between align-items-center py-5\">".concat(val.observation_name, "<span class=\"remove-observation\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"Delete\" data-id=\"").concat(val.id, "\"><a href=\"javascript:void(0)\"><i class=\"fas fa-trash text-danger\"></i></a></span></li>"));
        });
      } else {
        $('#observationLists').append("<p class=\"text-center fw-bold text-muted mt-3\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
      }
    },
    complete: function complete() {
      $('#observationSubmitBtn').attr('disabled', false);
    }
  });
});

// Delete Visit Observation Data
listenClick('.remove-observation', function (e) {
  e.preventDefault();
  var id = $(this).attr('data-id');
  var observationDeleteUrl = $('#doctorLogin').val() ? route('doctors.visits.delete.observation', id) : route('delete.observation', id);
  $(this).closest('li').remove();
  $.ajax({
    url: observationDeleteUrl,
    type: 'POST',
    dataType: 'json',
    success: function success(result) {
      if (result.success) {
        if ($('#observationLists li').length < 1) {
          displaySuccessMessage(result.message);
          $('#observationLists').append("<p class=\"text-center fw-bold mt-3 text-muted text-gray-600\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
        } else {
          displaySuccessMessage(result.message);
        }
      }
    }
  });
});

// Add visit Note Data
listenSubmit('#addVisitNote', function (e) {
  e.preventDefault();
  var noteName = $('#noteName').val();
  var empty2 = noteName.trim().replace(/ \r\n\t/g, '') === '';
  if (empty2) {
    displayErrorMessage(Lang.get('js.note_white_space'));
    return false;
  }
  var btnSubmitEle = $(this).find('#noteSubmitBtn');
  setAdminBtnLoader(btnSubmitEle);
  var noteAddUrl = $('#doctorLogin').val() ? route('doctors.visits.add.note') : route('add.note');
  $.ajax({
    url: noteAddUrl,
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function success(result) {
      $('ul#noteLists').empty();
      if (result.data.length > 0) {
        displaySuccessMessage(result.message);
        $.each(result.data, function (i, val) {
          $('#noteName').val('');
          $('#noteLists').append("<li class=\"list-group-item text-break text-wrap d-flex justify-content-between align-items-center py-5\">".concat(val.note_name, "<span class=\"remove-note\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"Delete\" data-id=\"").concat(val.id, "\"><a href=\"javascript:void(0)\"><i class=\"fas fa-trash text-danger\"></i></a></span></li>"));
        });
      } else {
        $('#noteLists').append("<p class=\"text-center fw-bold text-muted mt-3\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
      }
    },
    complete: function complete() {
      $('#noteSubmitBtn').attr('disabled', false);
    }
  });
});

// Delete Visit Note Data
listenClick('.remove-note', function (e) {
  e.preventDefault();
  var id = $(this).attr('data-id');
  $(this).closest('li').remove();
  var noteDeleteUrl = $('#doctorLogin').val() ? route('doctors.visits.delete.note', id) : route('delete.note', id);
  $.ajax({
    url: noteDeleteUrl,
    type: 'POST',
    dataType: 'json',
    success: function success(result) {
      if (result.success) {
        if ($('#noteLists li').length < 1) {
          displaySuccessMessage(result.message);
          $('#noteLists').append("<p class=\"text-center fw-bold mt-3 text-muted text-gray-600\">".concat($('#noRecordsFoundMSG').val(), "</p>"));
        } else {
          displaySuccessMessage(result.message);
        }
      }
    }
  });
});

// Add visit Prescription Data
listenSubmit('#addPrescription', function (e) {
  e.preventDefault();
  var btnSubmitEle = $(this).find('#prescriptionSubmitBtn');
  setAdminBtnLoader(btnSubmitEle);
  var prescriptionAddUrl = $('#doctorLogin').val() ? route('doctors.visits.add.prescription') : route('add.prescription');
  $.ajax({
    url: prescriptionAddUrl,
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function success(result) {
      $('#addPrescription')[0].reset();
      $('.visit-prescriptions').empty();
      $('#prescriptionId').val('');
      $.each(result.data, function (i, val) {
        var data = [{
          'id': val.id,
          'name': val.prescription_name,
          'frequency': val.frequency,
          'duration': val.duration
        }];
        var visitPrescriptionTblData = prepareTemplateRender('#visitsPrescriptionTblTemplate', data);
        $('.visit-prescriptions').append(visitPrescriptionTblData);
      });
      $('#addVisitPrescription').removeClass('show');
      displaySuccessMessage(result.message);
    },
    error: function error(result) {
      displayErrorMessage(result.responseJSON.message);
    },
    complete: function complete() {
      $('#prescriptionSubmitBtn').attr('disabled', false);
    }
  });
});

// Edit Visit Prescription Data
function renderData(id) {
  var prescriptionEditUrl = $('#doctorLogin').val() ? route('doctors.visits.edit.prescription', id) : route('edit.prescription', id);
  $.ajax({
    url: prescriptionEditUrl,
    type: 'GET',
    success: function success(result) {
      $('#addPrescription')[0].reset();
      $('#prescriptionId').val(result.data.id);
      $('#prescriptionNameId').val(result.data.prescription_name);
      $('#frequencyId').val(result.data.frequency);
      $('#durationId').val(result.data.duration);
      $('#descriptionId').val(result.data.description);
    }
  });
}
listenClick('.edit-prescription-btn', function () {
  var id = $(this).attr('data-id');
  if (!$('#addVisitPrescription').hasClass('show')) {
    $('#addVisitPrescription').addClass('show');
  }
  renderData(id);
});

// Delete Visit Prescription Data
listenClick('.delete-visit-prescription-btn', function (e) {
  e.preventDefault();
  var id = $(this).attr('data-id');
  $(this).closest('tr').remove();
  var prescriptionDeleteUrl = $('#doctorLogin').val() ? route('doctors.visits.delete.prescription', id) : route('delete.prescription', id);
  $.ajax({
    url: prescriptionDeleteUrl,
    type: 'POST',
    dataType: 'json',
    success: function success(result) {
      $('#addPrescription')[0].reset();
      $('#prescriptionId').val('');
      if (result.data.length < 1) {
        $('#addVisitPrescription').removeClass('show');
        displaySuccessMessage(result.message);
        $('.visit-prescriptions').append("<tr><td colspan=\"4\" class=\"text-center fw-bold  text-muted text-gray-600\">No data available in table</td></tr>");
      } else {
        $('#addVisitPrescription').removeClass('show');
        displaySuccessMessage(result.message);
      }
    }
  });
});

// Reset Form JS
listenClick('.reset-form', function () {
  $('#addPrescription')[0].reset();
});

/***/ }),

/***/ "./resources/assets/js/visits/visits.js":
/*!**********************************************!*\
  !*** ./resources/assets/js/visits/visits.js ***!
  \**********************************************/
/***/ (() => {

listenClick('.visit-delete-btn', function (event) {
  var visitRecordId = $(event.currentTarget).attr('data-id');
  deleteItem(route('visits.destroy', visitRecordId), Lang.get('js.visits'));
});

/***/ }),

/***/ "./resources/assets/scss/bill-pdf.scss":
/*!*********************************************!*\
  !*** ./resources/assets/scss/bill-pdf.scss ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/scss/custom-pages-dark.scss":
/*!******************************************************!*\
  !*** ./resources/assets/scss/custom-pages-dark.scss ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/scss/pages.scss":
/*!******************************************!*\
  !*** ./resources/assets/scss/pages.scss ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/scss/prescription-pdf.scss":
/*!*****************************************************!*\
  !*** ./resources/assets/scss/prescription-pdf.scss ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/assets/scss/smart-card-pdf.scss":
/*!***************************************************!*\
  !*** ./resources/assets/scss/smart-card-pdf.scss ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/pages": 0,
/******/ 			"assets/css/smart-card-pdf": 0,
/******/ 			"assets/css/prescription-pdf": 0,
/******/ 			"assets/css/bill-pdf": 0,
/******/ 			"assets/css/pages": 0,
/******/ 			"assets/css/custom-pages-dark": 0,
/******/ 			"css/app": 0,
/******/ 			"css/front-pages": 0,
/******/ 			"assets/front/css/about": 0,
/******/ 			"assets/front/css/front-custom": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/helper.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/custom.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/input_price_format.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/sidebar_menu.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/profile/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctors/doctors.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctors/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctors/detail.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/patients/detail.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/patients/doctor-patient-appointment.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/users/user-profile.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/patients/patients.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/patients/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/countries/countries.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/states/states.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/cities/cities.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_sessions/doctor_sessions.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_sessions/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/service_categories/service_categories.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/specializations/specializations.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/roles/roles.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/roles/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/settings/settings.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/services/services.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/services/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/appointments/appointments.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/appointments/patient-appointments.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/appointments/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/staff/staff.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/staff/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/dashboard/dashboard.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/dashboard/doctor-dashboard.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_appointments/doctor_appointments.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_appointments/calendar.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/appointments/patient-calendar.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/appointments/calendar.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/phone-number-country-code.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/currencies/currencies.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/visits/visits.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/visits/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/visits/doctor-visit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/clinic_schedule/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/visits/show-page.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/sliders/slider.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/sliders/create-edit-slider.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/medical-contact/enquiry.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/subscribers/create.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/enquiries/enquiry.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/subscribers/subscriber.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/cms/create.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/appointments/book_appointment.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/patient_visits/patient-visit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/transactions/transactions.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/transactions/patient-transactions.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/fronts/front_home/front-home.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/reviews/review.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/front/js/front-language.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/custom/create-account.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_holiday/doctor_holiday.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_holiday/holiday.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/doctor_holiday/Create_edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/category/category.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/brands/brands.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/medicines/medicines.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/purchase-medicine/purchase-medicine.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/medicine_bills/medicine_bill.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/prescriptions/create-edit.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/js/prescriptions/prescriptions.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/scss/custom-pages-dark.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/scss/pages.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/scss/bill-pdf.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/scss/prescription-pdf.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/scss/smart-card-pdf.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/front/scss/front-custom.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/front/scss/about.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/assets/front/scss/main.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/css/smart-card-pdf","assets/css/prescription-pdf","assets/css/bill-pdf","assets/css/pages","assets/css/custom-pages-dark","css/app","css/front-pages","assets/front/css/about","assets/front/css/front-custom"], () => (__webpack_require__("./resources/css/app.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;