// ** I18N
// Calendar PL language
// Author: Artur Filipiak, <imagen@poczta.fm>
// January, 2004
// Update: Tomasz Osmialowski <ichtis@gmail.com>
// July, 2005
// Encoding: iso-8859-2
Calendar._DN = new Array
("Niedziela", "Poniedzia³ek", "Wtorek", "¦roda", "Czwartek", "Pi±tek", "Sobota", "Niedziela");

Calendar._SDN = new Array
("N", "Pn", "Wt", "¦r", "Cz", "Pt", "So", "N");

Calendar._MN = new Array
("Styczeñ", "Luty", "Marzec", "Kwiecieñ", "Maj", "Czerwiec", "Lipiec", "Sierpieñ", "Wrzesieñ", "Pa¼dziernik", "Listopad", "Grudzieñ");

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

Calendar._TT["PREV_YEAR"] = "Poprz. rok (przytrzymaj dla menu)";
Calendar._TT["PREV_MONTH"] = "Poprz. miesi±c (przytrzymaj dla menu)";
Calendar._TT["GO_TODAY"] = "Poka¿ dzi¶";
Calendar._TT["NEXT_MONTH"] = "Nast. miesi±c (przytrzymaj dla menu)";
Calendar._TT["NEXT_YEAR"] = "Nast. rok (przytrzymaj dla menu)";
Calendar._TT["SEL_DATE"] = "Wybierz datê";
Calendar._TT["DRAG_TO_MOVE"] = "Przesuñ okienko";
Calendar._TT["PART_TODAY"] = " (dzi¶)";
Calendar._TT["MON_FIRST"] = "Poka¿ Poniedzia³ek jako pierwszy";
Calendar._TT["SUN_FIRST"] = "Poka¿ Niedzielê jako pierwsz±";
Calendar._TT["CLOSE"] = "Zamknij";
Calendar._TT["TODAY"] = "Dzi¶";
Calendar._TT["TIME_PART"] = "(Shift-)klik | drag, aby zmieniæ warto¶æ";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y.%m.%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Prezentacja pierwszych %s dni";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";
