
// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// Calendar PL language
// Author: Artur Filipiak, <imagen@poczta.fm>
// January, 2004
// Update: Tomasz Osmialowski <ichtis@gmail.com>
// July, 2006
// Encoding: iso-8859-2
// ** I18N

// full day names
Calendar._DN = new Array
("Niedziela", "Poniedzia³ek", "Wtorek", "¦roda", "Czwartek", "Pi±tek", "Sobota", "Niedziela");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("N", "Pn", "Wt", "¦r", "Cz", "Pt", "So", "N");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Styczeñ", "Luty", "Marzec", "Kwiecieñ", "Maj", "Czerwiec", "Lipiec", "Sierpieñ", "Wrzesieñ", "Pa¼dziernik", "Listopad", "Grudzieñ");

// short month names
Calendar._SMN = new Array
("Sty", "Lut", "Mar", "Kwi", "Maj", "Cze", "Lip", "Sie", "Wrz", "Pa¼", "Lis", "Gru");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O kalendarzu";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Wybór daty:\n" +
"- aby wybraæ rok u¿yj przycisków \xab, \xbb\n" +
"- aby wybraæ miesi±c u¿yj przycisków " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + "\n" +
"- aby przyspieszyæ wybór przytrzymaj wci¶niêty przycisk myszy nad ww. przyciskami.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Wybór czasu:\n" +
"- aby zwiêkszyæ warto¶æ kliknij na dowolnym elemencie selekcji czasu\n" +
"- aby zmniejszyæ warto¶æ u¿yj dodatkowo klawisza Shift\n" +
"- mo¿esz równie¿ poruszaæ myszkê w lewo i prawo wraz z wci¶niêtym lewym klawiszem.";

Calendar._TT["PREV_YEAR"] = "Poprzedni rok lub rozwijana lista";
Calendar._TT["PREV_MONTH"] = "Poprzedni miesi±c lub rozwijana lista";
Calendar._TT["GO_TODAY"] = "Poka¿ dzi¶";
Calendar._TT["NEXT_MONTH"] = "Nastêpny miesi±c lub rozwijana lista";
Calendar._TT["NEXT_YEAR"] = "Nastêpny rok lub rozwijana lista";
Calendar._TT["SEL_DATE"] = "Wybierz datê";
Calendar._TT["DRAG_TO_MOVE"] = "Przesuñ okienko";
Calendar._TT["PART_TODAY"] = " (dzi¶)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s jako pierwszy dzieñ tygodnia";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zamknij";
Calendar._TT["TODAY"] = "Dzi¶";
Calendar._TT["TIME_PART"] = "Naci¶nij SHIFT i przytrzymaj, aby zmieniæ warto¶æ";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d.%m.%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Godzina:";
