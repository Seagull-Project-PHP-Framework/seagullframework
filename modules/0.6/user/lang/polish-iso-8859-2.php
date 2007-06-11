<?php
/*
  Language-file polish-iso-8859-2.php
  Author: Tomasz Osmialowski <ichtis@gmail.com>
*/
$words = array(

/* Account MGR */

    // title
    'My Account'                   => 'Moje konto',
    'My Profile :: Edit'           => 'Mój profil :: edycja',

    // summary form (admin template)
    'Role'                         => 'Rola',
    'Date Registered'              => 'Data rejestracji',
    'Last Login'                   => 'Ostatnie logowanie',
    'first login in progress'      => 'pierwsza sesja',
    'Current IP Address'           => 'Adres IP',
    'change password'              => 'zmieñ has³o',
    // + user template
    'My Profile'                   => 'Mój Profil',
    'Preferences'                  => 'Konfiguracja',
    'Password'                     => 'Has³o',
    'edit preferences'             => 'konfiguracja',
    'view profile'                 => 'poka¿ profil',
    'No results found for that ID' => 'Nie znaleziono wyników o tym ID',

    // profile form (admin template)
    'Personal Details'        => 'Szczegó³y',
    'Username'                => 'U¿ytkownik',
    'First Name'              => 'Imiê',
    'Last Name'               => 'Nazwisko',
    'Contact'                 => 'Kontakt',
    'Location'                => 'Lokalizacja',
    'Address 1'               => 'Adres 1',
    'Address 2'               => 'Adres 2',
    'Address 3'               => 'Adres 3',
    'City'                    => 'Miasto',
    'Country'                 => 'Pañstwo',
    'Telephone'               => 'Telefon',
    'Mobile'                  => 'Tel. komórkowy',
    'County/State/Province'   => 'Województwo',
    'ZIP/Postal Code'         => 'Kod pocztowy',
    // + user template
    'Company'                 => 'Nazwa Firmy',
    'edit these values'       => 'Edycja',

    // edit form
    'edit user'          => 'Edycja u¿ytkownika',
    'Confirm Password'   => 'Potwierd¼ has³o',
    'Organisation name'  => 'Nazwa organizacji',
    'Is Active?'         => 'Czy aktywny?',
    'Security'           => 'Bezpieczeñstwo',
    'Security question'  => 'Pytanie bezpieczeñstwa',
    'Answer'             => 'Odpowied¼',

    'aSecurityQuestions' => array(
        0 => '',
        1 => 'Imiê ulubionego zwierzêcia',
        2 => 'Wa¿na data (dd.mm.rrrr)',
        3 => 'Wa¿ne miejsce',
        4 => 'Nazwisko panieñskie matki',
        5 => 'Ulubiony film',
        6 => 'Ulubiona piosenka',
        7 => 'Ulubiony napój'
    ),

    // messages
    'profile successfully updated' => 'Szczegó³y profilu zosta³y poprawnie zapisane',

    // validate
    'You must enter a username'             => 'Musisz podaæ nazwê u¿ytkownika',
    'username min length'                   => 'Nazwa u¿ytkownika musi zawieraæ wiêcej ni¿ 5 alfanumerycznych znaków bez odstêpów', // 'Your username must be alphanumeric with at least 5 characters and no spaces',

    'You must enter at least address 1'     => 'Musisz podaæ co najmniej jeden adres',
    'You must enter your city'              => 'Musisz podaæ miasto',
    'You must enter your state/province'    => 'Musisz podaæ województwo',

    'Please enter a company name'           => 'Proszê podaæ nazwê firmy',
    'You must enter your ZIP/Postal Code'   => 'Musisz podaæ kod pocztowy',
    'You must enter your province'          => 'Musisz podaæ województwo',
    'You must enter your country'           => 'Musisz podaæ pañstwo',
    'Your email is not correctly formatted' => 'Z³y format emaila',
    'You must enter your email'             => 'Musisz podaæ email',
    'You must choose a security question'   => 'Musisz wybraæ pytanie bezpieczeñstwa',
    'Please enter a telephone number'       => 'Musisz podaæ numer telefonu',
    'You must provide a security answer'    => 'Musisz podaæ odpowied¼ na wybrane pytanie bezpieczeñstwa',


/* Register MRG */

    // title
    'Register' => 'Rejestracja',

    // validation
    'You must enter a password'                                    => 'Musisz podaæ has³o',
    'Please confirm password'                                      => 'Proszê potwierdziæ has³o',
    'Password must be between 5 to 10 characters'                  => 'Has³o musi zawieraæ od 5 do 10 znaków',
    'Passwords are not the same'                                   => 'Has³a nie s± takie same',
    'This email already exist in the DB, please choose another'    => 'Podany email ju¿ istnieje w bazie danych, proszê podaæ inny',
    'This username already exist in the DB, please choose another' => 'Podana nazwa u¿ytkownika ju¿ istnieje w bazie danych, proszê podaæ inn±',

    // messages
    'user successfully registered' => 'Rejestracja przebieg³a poprawnie. Proszê sprawdziæ swój email z pro¶b± o potwierdzenie has³a',

    // XXX mail templates here


/* Password MRG */

    // titles
    'Retrieve password' => 'Odzyskaj has³o',

    // list
    'Enter the email address you registered with' => 'Podaj adres email podany przy rejestracji',
    'retrieve' => 'odzyskaj',


/* UserPassword */

    // titles
    'Change Password' => 'Zmiana has³a',

    // form
    'Original password'  => 'Aktualne has³o',
    'New password'       => 'Nowe has³o',
    'Confirm'            => 'Potwierd¼',
    'Notify me by email' => 'Prze¶lij na email',

    // messages
    'Password updated successfully' => 'Has³o zosta³o zmienione',

    // validation
    'You must enter your original password'                 => 'Musisz podaæ aktualne has³o',
    'You must enter a new password'                         => 'Musisz podaæ nowe has³o',
    'You have entered your original password incorrectly'   => 'Podane aktualne has³o jest niepoprawne',


/* Login MRG */

    // title
    'Login' => 'Zaloguj',

    // form
    'Authorisation Required' => 'Wymagana autoryzacja dostêpu',

    // validation
    'username/password not recognized' => 'Podana nazwa u¿ytkownika i has³o nie zosta³y poprawnie rozpoznane, proszê spróbowaæ ponownie', // 'Your username/password combination has not been recognized, please try again',

    // XXX: other messages are defined in default lang file for now :/


/* UserPreference MGR */

    // title
    'User Preferences' => 'Konfiguracja u¿ytkownika',

    // form (admin template)
    'Theme'                                        => 'Szablon',
    'Change what this site looks like'             => 'Okre¶la wygl±d ca³ego serwisu',
    'Date format'                                  => 'Format daty',
    'UK format is dd/mm/yyyy and US is mm/dd/yyyy' => 'Format UK to dd/mm/rrrr, a US  to mm/dd/rrrr',
    'Interface language'                           => 'Jêzyk interfejsu',
    'Session timeout'                              => 'Czas trwania sesji',
    'Session timeout tooltip'                      => 'Dla bezpieczeñstwa czas trwania sesji bêdzie trwa³, a¿ do zamkniêcia przegl±darki, jednak mo¿esz okre¶liæ maksymalny czas jej trwania', // 'As a security measure your session will end once you close the browser, however, you can set the max duration of your session within this time',
    'Locale'                                       => 'Lokalizacja',
    'Timezone'                                     => 'Strefa czasowa',
    'Results per page'                             => 'Ilo¶æ wyników na stronie',
    'Show execution times?'                        => 'Pokazaæ czas wykonania skryptu?',
    // + user template
    'General'                                      => 'Ogólne',

    // messages
    'details successfully updated' => 'Dane zosta³y zapisane',


/* Profile MGR */

    // titile
    'User Profile' => 'Profil u¿ytkownika',

    // form
    'Real Name'                => 'Nazwisko i imiê',
    'Lives in'                 => 'Mieszka w',
    'Member Since'             => 'Uczestnik od',
    'none given'               => 'b/d',
    'Posting Stats for User'   => 'Ustaw status dla u¿ytkownika',
    'Total Articles'           => 'Wszystkich artyku³ów',
    'Total Comments'           => 'Wszystkich komentarzy',
    'coming soon ...'          => 'wkrótce dostêpne ...',

     // messaging
    'back to contacts' => 'powrót do kontaktów',
    'Message'          => 'Wiadomo¶æ',
    'send message'     => 'wy¶lij wiadomo¶æ',
    'Contacts'         => 'Kontakty',
    'add to contacts'  => 'dodaj do kontaktów',


/* OrgPreferences MGR */

    // title
    'Organisation Preferences' => 'Konfiguracja organizacji',

    // form
    'Preferences for org' => 'Konfiguracja organizacji',

    // messages
    'org details successfully updated' => 'Szczegó³y organizacji zosta³y zmienione',


/* OrgType MGR */

    // titles
    'OrgType Manager'           => 'Menad¿er typu organizacji',
    'OrgType Manager :: Browse' => 'Menad¿er typu organizacji :: Podgl±d',
    'OrgType Manager :: Add'    => 'Menad¿er typu organizacji :: Dodaj',
    'OrgType Manager :: Edit'   => 'Menad¿er typu organizacji :: Edycja',

    // list
    'Organisation Type list'    => 'Lista typów organizacji',
    'Add organisation type'     => 'Dodaj typ organizacji',
    'organisation type'         => 'typ organizacji',
    'Be Careful!'               => 'B±d¼ ostro¿ny!',

    // form
    'New organisation'          => 'Nowa organizacja',
    'New organisation type'     => 'Nowy typ organizacji',
    'Edit organisation'         => 'Edycja organizacji',

    // validation
    'You must enter an organisation type name' => 'Musisz podaæ nazwê organizacji',

    // messages
    'No data was updated'                             => 'Brak danych do zmiany',
    'Org type(s) deleted successfully'                => 'Typ organizacji zosta³ usuniêty',
    'Organisation type saved successfully'            => 'Typ organizacji zosta³ zapisany',
    'Organisation type has been updated successfully' => 'Typ organizacji zosta³ zmieniony',
    'No organisations found' => 'Nie znaleziono organizacji',


/* Org MGR */

    // title
    'Organisation Manager'           => 'Menad¿er Organizacji',
    'Organisation Manager :: Browse' => 'Menad¿er Organizacji :: Podgl±d',
    'Organisation Manager :: Add'    => 'Menad¿er Organizacji :: Dodaj',
    'Organisation Manager :: Edit'   => 'Menad¿er Organizacji :: Edycja',

    // list
    'Organisation list'    => 'Lista organizacji',
    'Website'              => 'Strona www',
    'change'               => 'zmieñ',
    'organisation'         => 'organizacja',

    // form
    'New organisation'     => 'Nowa organizacja',
    'Edit organisation'    => 'Edycja organizacji',
    'Details'              => 'Szczegó³y',
    'Description'          => 'Opis',
    'Parent Org'           => 'Nadrzêdna organizacja',
    'Default Role'         => 'Standardowa rola',

    // validation
    'You must enter an organisation name' => 'Musisz podaæ nazwê organizacji',

    // messages
    'organisation successfully added'                                                     => 'Organizacja zosta³a dodana',
    'The selected organisation(s) have successfully been deleted'                         => 'Zaznaczone organizacje zosta³y usuniête',
    'The selected organisation cannot be deleted because there are users relating to it'  => 'Zaznaczone %s organizacje nie zosta³y usuniête, bo istnij± u¿ytkownicy przy³±czeni do niej!',
    'The organisation has successfully been updated'                                      => 'Organizacja zosta³a zmieniona',
    'The organisation has successfully been updated, no data changed'                     => 'Organizacja zosta³a zmieniona, bez zmiany danych',


/* Preference MGR */

    // titles
    'Preference Manager'           => 'Menad¿er Preferencji',
    'Preference Manager :: Browse' => 'Menad¿er Preferencji :: Podgl±d',
    'Preference Manager :: Add'    => 'Menad¿er Preferencji :: Dodaj',
    'Preference Manager :: Edit'   => 'Menad¿er Preferencji :: Edycja',

    // list
    'Default value' => 'Standardowa warto¶æ',
    'preference'    => 'preferencja',

    // form
    'New preference'     => 'Nowa preferencja',
    'Editing preference' => 'Edycja preferencji',

    // validation
    'You must enter a default value'   => 'Musisz podaæ standardow± warto¶æ',
    'You must enter a preference name' => 'Musisz podaæ nazwê preferencji',

    // messages
    'pref successfully deleted' => 'Preferencja zosta³a wykasowana',
    'pref successfully updated' => 'Preferencja zosta³a zmieniona',
    'pref successfully added'   => 'Preferencja zosta³a dodana',


/* Permission MGR */

    // titles
    'Permission Manager'                    => 'Menad¿er Uprawnieñ',
    'Permission Manager :: Browse'          => 'Menad¿er Uprawnieñ :: Podgl±d',
    'Permission Manager :: Add'             => 'Menad¿er Uprawnieñ :: Dodaj',
    'Permission Manager :: Edit'            => 'Menad¿er Uprawnieñ :: Edycja',
    'Permission Manager :: Detect & Add'    => 'Menad¿er Uprawnieñ :: Wykryj i dodaj',
    'Permission Manager :: Detect Orphaned' => 'Menad¿er Uprawnieñ :: Wykryj osierocone',

    // list
    'New permission'           => 'Nowe uprawnienie',
    'Editing permission'       => 'Edycja uprawnieñ',
    'detect & add permissions' => 'wykryj i dodaj',
    'remove orphaned'          => 'usuñ osierocone',
    'filter by module'         => 'filtruj wg modu³u',
    'all'                      => 'wszystkie',
    'permission'               => 'uprawnienie',

    // add / edit form
    'Module'                   => 'Modu³',

    // detect perms / remove orphaned
    'Detected Perms'             => 'Wykryj uprawnienia',
    'Detected Orphaned Perms'    => 'Wykryj osierocone uprawnienia',
    'orphaned perms description' => 'Wy¶wietla uprawnienia zapisane w bazie, ale które nie istniej± w modu³ach.', // 'The listed permissions were detected in the database, but do not exist in the modules.',
    'detected perms description' => 'Wy¶wietla uprawnienia wykryte w modu³ach, ale które nie zosta³y dodane jeszcze do bazy.', // 'The listed permissions were detected in the modules, but have not been added to the system yet.',

    // validation
    'You must enter a permission name' => 'Musisz podaæ nazwê uprawnienia',

    // messages
    'perm already defined'                      => 'Uprawnienie ju¿ zosta³o zdefiniowane',
    'perm successfully added'                   => 'Uprawnienia zosta³o dodane',
    'perm(s) successfully added'                => 'Uprawnienia zosta³y dodane',
    'perm successfully deleted'                 => 'Uprawnienia zosta³y usuniête',
    'perm successfully updated'                 => 'Uprawnienia zosta³y zmienione',

/* Role MGR */

    // titles
    'Role Manager'                => 'Menad¿er Ról',
    'Role Manager :: Browse'      => 'Menad¿er Ról :: Podgl±d',
    'Role Manager :: Add'         => 'Menad¿er Ról :: Dodaj',
    'Role Manager :: Edit'        => 'Menad¿er Ról :: Edycja',
    'Role Manager :: Permissions' => 'Menad¿er Ról :: Uprawnienia',

    // list
    'New Role'   => 'Nowa rola',
    'duplicate'  => 'powiel',
    'role'       => 'rola',
    'permission' => 'uprawnienie',

    // add / edit / perms assignment
    'Editing role'                        => 'Edycja ról',
    'Changing permission assignments for' => 'Zmiana uprawnieñ przypisanych dla',
    'Remaining permissions'               => 'Pozosta³e uprawnienia',
    'Selected permissions for'            => 'Zaznaczone uprawnienia dla',
    'remove'                              => 'usuñ',

    // validation
    'You must enter a role name' => 'Musisz podaæ nazwê roli',

    // messages
    'role successfully added'                                                             => 'Rola zosta³a dodana',
    'role successfully updated'                                                           => 'Rola zosta³a zmieniona',
    'role successfully deleted'                                                           => 'Rola zosta³a usuniêta',
    'role successfully deleted but please note, admin/unassigned roles cannot be deleted' => 'Rola zosta³a usuniêta, ale proszê zauwa¿yæ, ¿e usuniêcie roli administratora nie jest mo¿liwe',
    'role successfully duplicated'                                                        => 'Rola zosta³a powielona',
    'role assignments successfully updated'                                               => 'Przypisanie roli zosta³o zmienione',


/* UserSearch MGR */

    // titles
    'User Manager :: Search' => 'Menad¿er u¿ytkownika :: Wyszukiwanie',

    // list
    'Search Criteria' => 'Kryteria wyszukiwania',
    'Role name'       => 'Nazwa roli',
    'User ID'         => 'ID u¿ytkownika',
    'Organisation'    => 'Organizacja',
    'Register Date'   => 'Data rejestracji',

    // validation
    'You must select a valid date' => 'Musisz podaæ poprawnie datê',
    'You must select a date'       => 'Musisz wybraæ datê',


/* UserImport MGR */

    // titles
    'User Import Manager' => 'Menad¿er importu u¿ytkowników',

    // list
    'userfile'                                                         => 'Wprowad¼ plik',
    'Use the document manager to upload CSV files.'                    => 'U¿yj menad¿er dokumentów do przes³ania plików CSV.',
    'Import users into selected organisation.'                         => 'Importuj u¿ytkowników do wybranej organizacji.',
    'User will be assigned to selected role.'                          => 'U¿ytkownik zosta³ przypisany do wybranej roli.',
    'You should first upload at least one csv file in your upload dir' => 'Powiniene¶ najpierw przes³aæ co najmniej jeden plik CSV do przes³ania',

    // validation
    'Please select a file.'              => 'Proszê wybraæ plik.',
    'Please select the organisation'     => 'Proszê wybraæ organizacjê',
    'Please select the role'             => 'Proszê wybraæ rolê',


/* User MGR */

    // titles
    'User Manager'                     => 'Menad¿er u¿ytkowników',
    'User Manager :: Browse'           => 'Menad¿er u¿ytkowników :: Przegl±d',
    'User Manager :: Login Data'       => 'Menad¿er u¿ytkowników :: Dane logowania',
    'User Manager :: Edit'             => 'Menad¿er u¿ytkowników :: Edycja',
    'User Manager :: Edit permissions' => 'Menad¿er u¿ytkowników :: Edycja uprawnieñ',
    'User Manager :: Add'              => 'Menad¿er u¿ytkowników :: Dodaj',
    'User Manager :: Reset password'   => 'Menad¿er u¿ytkowników :: Resetuj has³o',
    'User Manager :: Change status'    => 'Menad¿er u¿ytkowników :: Zmieñ status',

    // list
    'New User'             => 'Nowy u¿ytkownik',
    'Search'               => 'Wyszukaj',
    'Import users'         => 'Importuj u¿ytkowników',
    'User list'            => 'Lista u¿ytkowników',
    'user(s) found'        => 'znaleziono u¿ytkowników',
    'clear search'         => 'wyczy¶æ wyszukiwanie',
    'Logins'               => 'Logowania',
    'list'                 => 'lista',
    'Change status'        => 'Zmieñ status',
    'No users found'       => 'Nie znaleziono u¿ytkowników',
    'Synchronise'          => 'Synchronizuj',
    '(each users current)' => '(ka¿dego u¿ytkownika do wybranej roli)',
    'sync perms with role' => 'synchronizuj uprawnienia',
    'add missing perms'    => 'dodaj brakuj±ce uprawnienia',
    'remove extra perms'   => 'usuñ dodatkowe uprawnienia',
    'complete sync'        => 'pe³na synchronizacja',

    // add / edit
    'add user'             => 'dodaj u¿ytkownika',
    'check if active'      => 'zaznacz, je¶li aktywny',

    // login data
    'Connection list'      => 'Lista po³±czeñ',
    'Login Time'           => 'Czas logowania',
    'Remote IP'            => 'Adres IP',
    'Are you sure?'        => 'Czy jeste¶ pewien?',

    // status change
    'Disable Now'          => 'Wy³±cz teraz',
    'Enable Now'           => 'W³±cz teraz',
    'active'               => 'aktywny',
    'disabled'             => 'wy³±czony',
    'Changing status for'  => 'Zmieñ status dla',
    'Current status is'    => 'Obecny status to',
    'Enable user'          => 'Uaktywnij u¿ytkownika',
    'Disable user'         => 'Wy³±cz u¿ytkownika',
    'Notify user'          => 'Powiadom u¿ytkownika emailem',

    // reset
    'Reset password now'                   => 'Resetuj has³o teraz',
    'Resetting password for'               => 'Resetuj has³o dla',
    'Reset password info'                  => 'Pamiêtaj, je¿eli resetujesz has³o dla administratora, musisz byæ pewny poprawno¶ci adresu email (mo¿esz to zrobiæ edytuj±c dane administratora), oraz ¿e system ma poprawnie skonfigurow± wysy³kê emaili.', // 'Remember, if you\'re resetting the password for the admin user, make sure you correctly specify the email address the new password will go to (hit "edit" for the admin user), and that your system is correctly setup to send and receive email.',

    // change permissions
    'for user id'            => 'dla u¿ytkownika o ID',
    'Select a module'        => 'Zaznacz modu³',

    // messages
    'user successfully added'              => 'U¿ytkownik zosta³ dodany',
    'Deleted successfully'                 => 'Zaznaczone wpisy zosta³y usuniête',
    'Status changed successfully'          => 'Status zosta³ zmieniony',
    'user successfully deleted'            => 'Zaznaczeni u¿ytkownicy zostali usuniêci',
    'The user has been successfully added' => 'U¿ytkownik zosta³ dodany',


/**
 * Mail templates
 */
    'Thanks for registering with'            => 'Dziêkujemy za zarejestrowanie',
    'username'                               => 'Nazwa u¿ytkownika',
    'Dear'                                   => 'Witaj',
    'You are being sent this email because your new account status is now' => 'Otrzyma³e¶ ten email, poniewa¿ twoje konto ma teraz status',
    'Click'                                  => 'Kliknij',
    'here'                                   => 'tutaj',
    'to logon to the'                        => 'aby siê zalogowaæ do',
    'site now'                               => 'strony.',
    'You are being sent this email because you just registered, your logon details are as follows' => 'Otrzyma³e¶ ten email poniewa¿ dokona³e¶ rejestracji, twoje parametry logowania s± dostêpne poni¿ej',
    'Your username is'                       => 'Twoja nazwa u¿ytkownika to',
    'Your password is'                       => 'Twoje has³o to',
    'Your registration is being reviewed'    => 'Twoja rejestracja zosta³a przyjêta, zostaniesz wkrótce powiadomiony',
    'Password Reminder'                      => 'Przypomnienie has³a',
    'You are being sent this email because'  => 'Otrzyma³e¶ ten email, poniewa¿ wybra³e¶ opcjê przypomnienia has³a.',
    'Your new password is'                   => 'Twoje has³o to',
    'New Registration at'                    => 'Nowa rejestracja w',
    'The following user has just registered' => 'U¿ytkownik zosta³ zarejestrowany',
    'to enable the new users account'        => 'uruchomienie konta nowego u¿ytkownika',

    // not found anywhere, but I suppose, that somewhere it is used
    'There was a problem with your session, please login again'      => 'Czas trwania sesji zosta³ przekroczony, proszê zalogowaæ siê ponownie',
    'Only logged on users have access to this area, please register' => 'Tylko zalogowani u¿ytkownicy maj± dostêp do tej strefy, proszê siê zalogowaæ',
    'Registration has been disabled'                                 => 'Rejestracja zosta³y wy³±czona',
);

?>
