//  Concatenates 2 strings (cleans the strings)
//  var titleOfPage string id of the input string to clean
//  var urlOfPage   string id of the output string to display
function cleanUpUri(input, titleOfPage, urlOfPage)
{
    var titleOfPage = document.getElementById(titleOfPage);
    var urlOfPage = document.getElementById(urlOfPage);

    var chaine1 = replaceUri(input); // ex: document.formulaire.saisie1
    urlOfPage.value = chaine1; // ex : output = document.formulaire.destination
    //refreshMetaTitle(input, titleOfPage);
}
//  Cleans up a string to be stored as an Uri
function replaceUri(cat)
{
    var chaine = cat;
    chaine = chaine.replace(/[יטךכ]/g,"e");
	chaine = chaine.replace(/[באגה]/g,"a");
	chaine = chaine.replace(/[םןמ]/g,"i");
	chaine = chaine.replace(/[תשûü]/g,"u");
	chaine = chaine.replace(/[ףצפ]/g,"o");
    chaine = chaine.replace(/(\w)[\W]+(\w)/g,"$1-$2");
    chaine = chaine.toLowerCase();
    return chaine;
}
function refreshMetaTitle(input, meta)
{
    inputField = replaceUri(document.getElementById(input).value);
    if (metaField = document.getElementById(meta)) {
        metaField.value = inputField;
    }
}
