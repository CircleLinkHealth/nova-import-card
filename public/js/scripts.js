(function(root, factory) {
    if(typeof exports === 'object') {
        module.exports = factory();
    }
    else if(typeof define === 'function' && define.amd) {
        define([], factory);
    }
    else {
        root['BlueButton'] = factory();
    }
}(this, function() {

/* BlueButton.js -- 0.4.2 */

/*
 * ...
 */

/* exported Core */
var Core = (function () {
  
  /*
   * ...
   */
  var parseData = function (source) {
    source = stripWhitespace(source);
    
    if (source.charAt(0) === '<') {
      try {
        return Core.XML.parse(source);
      } catch (e) {
        if (console.log) {
          console.log("File looked like it might be XML but couldn't be parsed.");
        }
      }
    }

    try {
      return JSON.parse(source);
    } catch (e) {
      if (console.error) {
        console.error("Error: Cannot parse this file. BB.js only accepts valid XML " +
          "(for parsing) or JSON (for generation). If you are attempting to provide " +
          "XML or JSON, please run your data through a validator to see if it is malformed.\n");
      }
      throw e;
    }
  };
  
  
  /*
   * Removes leading and trailing whitespace from a string
   */
  var stripWhitespace = function (str) {
    if (!str) { return str; }
    return str.replace(/^\s+|\s+$/g,'');
  };
  
  
  /*
   * A wrapper around JSON.stringify which allows us to produce customized JSON.
   *
   * See https://developer.mozilla.org/en-US/docs/Web/
   *        JavaScript/Guide/Using_native_JSON#The_replacer_parameter
   * for documentation on the replacerFn.
   */
  var json = function () {

    var datePad = function(number) {
      if (number < 10) {
        return '0' + number;
      }
      return number;
    };
    
    var replacerFn = function(key, value) {
      /* By default, Dates are output as ISO Strings like "2014-01-03T08:00:00.000Z." This is
       * tricky when all we have is a date (not a datetime); JS sadly ignores that distinction.
       *
       * To paper over this JS wart, we use two different JSON formats for dates and datetimes.
       * This is a little ugly but makes sure that the dates/datetimes mostly just parse
       * correclty for clients:
       *
       * 1. Datetimes are rendered as standard ISO strings, without the misleading millisecond
       *    precision (misleading because we don't have it): YYYY-MM-DDTHH:mm:ssZ
       * 2. Dates are rendered as MM/DD/YYYY. This ensures they are parsed as midnight local-time,
       *    no matter what local time is, and therefore ensures the date is always correct.
       *    Outputting "YYYY-MM-DD" would lead most browsers/node to assume midnight UTC, which
       *    means "2014-04-27" suddenly turns into "04/26/2014 at 5PM" or just "04/26/2014"
       *    if you format it as a date...
       *
       * See http://stackoverflow.com/questions/2587345/javascript-date-parse and
       *     http://blog.dygraphs.com/2012/03/javascript-and-dates-what-mess.html
       * for more on this issue.
       */
      var originalValue = this[key]; // a Date

      if ( value && (originalValue instanceof Date) && !isNaN(originalValue.getTime()) ) {

        // If while parsing we indicated that there was time-data specified, or if we see
        // non-zero values in the hour/minutes/seconds/millis fields, output a datetime.
        if (originalValue._parsedWithTimeData ||
            originalValue.getHours() || originalValue.getMinutes() ||
            originalValue.getSeconds() || originalValue.getMilliseconds()) {

          // Based on https://developer.mozilla.org/en-US/docs/Web/JavaScript/
          //    Reference/Global_Objects/Date/toISOString
          return originalValue.getUTCFullYear() +
            '-' + datePad( originalValue.getUTCMonth() + 1 ) +
            '-' + datePad( originalValue.getUTCDate() ) +
            'T' + datePad( originalValue.getUTCHours() ) +
            ':' + datePad( originalValue.getUTCMinutes() ) +
            ':' + datePad( originalValue.getUTCSeconds() ) +
            'Z';
        }
        
        // We just have a pure date
        return datePad( originalValue.getMonth() + 1 ) +
          '/' + datePad( originalValue.getDate() ) +
          '/' + originalValue.getFullYear();

      }

      return value;
    };
    
    return JSON.stringify(this, replacerFn, 2);
  };
  
  
  /*
   * Removes all `null` properties from an object.
   */
  var trim = function (o) {
    var y;
    for (var x in o) {
      if (o.hasOwnProperty(x)) {
        y = o[x];
        // if (y === null || (y instanceof Object && Object.keys(y).length == 0)) {
        if (y === null) {
          delete o[x];
        }
        if (y instanceof Object) y = trim(y);
      }
    }
    return o;
  };
  
  
  return {
    parseData: parseData,
    stripWhitespace: stripWhitespace,
    json: json,
    trim: trim
  };
  
})();
;

/*
 * ...
 */

Core.Codes = (function () {
  
  /*
   * Administrative Gender (HL7 V3)
   * http://phinvads.cdc.gov/vads/ViewValueSet.action?id=8DE75E17-176B-DE11-9B52-0015173D1785
   * OID: 2.16.840.1.113883.1.11.1
   */
  var GENDER_MAP = {
    'F': 'female',
    'M': 'male',
    'UN': 'undifferentiated'
  };
  
  /*
   * Marital Status (HL7)
   * http://phinvads.cdc.gov/vads/ViewValueSet.action?id=46D34BBC-617F-DD11-B38D-00188B398520
   * OID: 2.16.840.1.114222.4.11.809
   */
  var MARITAL_STATUS_MAP = {
    'N': 'annulled',
    'C': 'common law',
    'D': 'divorced',
    'P': 'domestic partner',
    'I': 'interlocutory',
    'E': 'legally separated',
    'G': 'living together',
    'M': 'married',
    'O': 'other',
    'R': 'registered domestic partner',
    'A': 'separated',
    'S': 'single',
    'U': 'unknown',
    'B': 'unmarried',
    'T': 'unreported',
    'W': 'widowed'
  };

  /*
   * Phone Types taken from HL7 Address Use
   * https://www.hl7.org/fhir/v3/AddressUse/index.html
   * OID: 2.16.840.1.113883.5.1119
   */
  var PHONE_MAP = {
    'H': 'home',
    'HP': 'home',
    'MC': 'mobile',
    'WP': 'work'
  };

  /*
   * Religious Affiliation (HL7 V3)
   * https://phinvads.cdc.gov/vads/ViewValueSet.action?id=6BFDBFB5-A277-DE11-9B52-0015173D1785
   * OID: 2.16.840.1.113883.5.1076
   */
  var RELIGION_MAP = {
    "1001": "adventist",
    "1002": "african religions",
    "1003": "afro-caribbean religions",
    "1004": "agnosticism",
    "1005": "anglican",
    "1006": "animism",
    "1061": "assembly of god",
    "1007": "atheism",
    "1008": "babi & baha'i faiths",
    "1009": "baptist",
    "1010": "bon",
    "1062": "brethren",
    "1011": "cao dai",
    "1012": "celticism",
    "1013": "christian (non-catholic, non-specific)",
    "1063": "christian scientist",
    "1064": "church of christ",
    "1065": "church of god",
    "1014": "confucianism",
    "1066": "congregational",
    "1015": "cyberculture religions",
    "1067": "disciples of christ",
    "1016": "divination",
    "1068": "eastern orthodox",
    "1069": "episcopalian",
    "1070": "evangelical covenant",
    "1017": "fourth way",
    "1018": "free daism",
    "1071": "friends",
    "1072": "full gospel",
    "1019": "gnosis",
    "1020": "hinduism",
    "1021": "humanism",
    "1022": "independent",
    "1023": "islam",
    "1024": "jainism",
    "1025": "jehovah's witnesses",
    "1026": "judaism",
    "1027": "latter day saints",
    "1028": "lutheran",
    "1029": "mahayana",
    "1030": "meditation",
    "1031": "messianic judaism",
    "1073": "methodist",
    "1032": "mitraism",
    "1074": "native american",
    "1075": "nazarene",
    "1033": "new age",
    "1034": "non-roman catholic",
    "1035": "occult",
    "1036": "orthodox",
    "1037": "paganism",
    "1038": "pentecostal",
    "1076": "presbyterian",
    "1039": "process, the",
    "1077": "protestant",
    "1078": "protestant, no denomination",
    "1079": "reformed",
    "1040": "reformed/presbyterian",
    "1041": "roman catholic church",
    "1080": "salvation army",
    "1042": "satanism",
    "1043": "scientology",
    "1044": "shamanism",
    "1045": "shiite (islam)",
    "1046": "shinto",
    "1047": "sikism",
    "1048": "spiritualism",
    "1049": "sunni (islam)",
    "1050": "taoism",
    "1051": "theravada",
    "1081": "unitarian universalist",
    "1052": "unitarian-universalism",
    "1082": "united church of christ",
    "1053": "universal life church",
    "1054": "vajrayana (tibetan)",
    "1055": "veda",
    "1056": "voodoo",
    "1057": "wicca",
    "1058": "yaohushua",
    "1059": "zen buddhism",
    "1060": "zoroastrianism"
  };

  /*
   * Race & Ethnicity (HL7 V3)
   * Full list at http://phinvads.cdc.gov/vads/ViewCodeSystem.action?id=2.16.840.1.113883.6.238
   * OID: 2.16.840.1.113883.6.238
   *
   * Abbreviated list closer to real usage at: (Race / Ethnicity)
   * https://phinvads.cdc.gov/vads/ViewValueSet.action?id=67D34BBC-617F-DD11-B38D-00188B398520
   * https://phinvads.cdc.gov/vads/ViewValueSet.action?id=35D34BBC-617F-DD11-B38D-00188B398520
   */
  var RACE_ETHNICITY_MAP = {
    '2028-9': 'asian',
    '2054-5': 'black or african american',
    '2135-2': 'hispanic or latino',
    '2076-8': 'native',
    '2186-5': 'not hispanic or latino',
    '2131-1': 'other',
    '2106-3': 'white'
  };

  /*
   * Role (HL7 V3)
   * https://phinvads.cdc.gov/vads/ViewCodeSystem.action?id=2.16.840.1.113883.5.111
   * OID: 2.16.840.1.113883.5.111
   */
  var ROLE_MAP = {
    "ACC": "accident site",
    "ACHFID":  "accreditation location identifier",
    "ACTMIL":  "active duty military",
    "AGNT": "agent",
    "ALL": "allergy clinic",
    "AMB": "ambulance",
    "AMPUT":   "amputee clinic",
    "ANTIBIOT":    "antibiotic",
    "ASSIST":  "assistive non-person living subject",
    "AUNT":    "aunt",
    "B":   "blind",
    "BF":  "beef",
    "BILL":    "billing contact",
    "BIOTH":   "biotherapeutic non-person living subject",
    "BL":  "broiler",
    "BMTC":    "bone marrow transplant clinic",
    "BMTU":    "bone marrow transplant unit",
    "BR":  "breeder",
    "BREAST":  "breast clinic",
    "BRO": "brother",
    "BROINLAW":    "brother-in-law",
    "C":   "calibrator",
    "CANC": "child and adolescent neurology clinic",
    "CAPC": "child and adolescent psychiatry clinic",
    "CARD": "ambulatory health care facilities; clinic/center; rehabilitation: cardiac facilities",
    "CAREGIVER": "care giver",
    "CAS": "asylum seeker",
    "CASM":    "single minor asylum seeker",
    "CATH":    "cardiac catheterization lab",
    "CCO": "clinical companion",
    "CCU": "coronary care unit",
    "CHEST":   "chest unit",
    "CHILD":   "child",
    "CHLDADOPT":   "adopted child",
    "CHLDFOST":    "foster child",
    "CHLDINLAW":   "child in-law",
    "CHR": "chronic care facility",
    "CLAIM":   "claimant",
    "CN":  "national",
    "CNRP":    "non-country member without residence permit",
    "CNRPM":   "non-country member minor without residence permit",
    "CO":  "companion",
    "COAG":    "coagulation clinic",
    "COCBEN":  "continuity of coverage beneficiary",
    "COMM":    "community location",
    "COMMUNITYLABORATORY": "community laboratory",
    "COUSN":   "cousin",
    "CPCA":    "permit card applicant",
    "CRIMEVIC":    "crime victim",
    "CRP": "non-country member with residence permit",
    "CRPM":    "non-country member minor with residence permit",
    "CRS": "colon and rectal surgery clinic",
    "CSC": "community service center",
    "CVDX":    "cardiovascular diagnostics or therapeutics unit",
    "DA":  "dairy",
    "DADDR":   "delivery address",
    "DAU": "natural daughter",
    "DAUADOPT":    "adopted daughter",
    "DAUC":    "daughter",
    "DAUFOST": "foster daughter",
    "DAUINLAW":    "daughter in-law",
    "DC":  "therapeutic class",
    "DEBR":    "debridement",
    "DERM":    "dermatology clinic",
    "DIFFABL": "differently abled",
    "DOMPART": "domestic partner",
    "DPOWATT": "durable power of attorney",
    "DR":  "draft",
    "DU":  "dual",
    "DX":  "diagnostics or therapeutics unit",
    "E":   "electronic qc",
    "ECHO":    "echocardiography lab",
    "ECON":    "emergency contact",
    "ENDO":    "endocrinology clinic",
    "ENDOS":   "endoscopy lab",
    "ENROLBKR":    "enrollment broker",
    "ENT": "otorhinolaryngology clinic",
    "EPIL":    "epilepsy unit",
    "ER":  "emergency room",
    "ERL": "enrollment",
    "ETU": "emergency trauma unit",
    "EXCEST":  "executor of estate",
    "EXT": "extended family member",
    "F":   "filler proficiency",
    "FAMDEP":  "family dependent",
    "FAMMEMB": "family member",
    "FI":  "fiber",
    "FMC": "family medicine clinic",
    "FRND":    "unrelated friend",
    "FSTUD":   "full-time student",
    "FTH": "father",
    "FTHINLAW":    "father-in-law",
    "FULLINS": "fully insured coverage sponsor",
    "G":   "group",
    "GACH":    "hospitals; general acute care hospital",
    "GD":  "generic drug",
    "GDF": "generic drug form",
    "GDS": "generic drug strength",
    "GDSF":    "generic drug strength form",
    "GGRFTH":  "great grandfather",
    "GGRMTH":  "great grandmother",
    "GGRPRN":  "great grandparent",
    "GI":  "gastroenterology clinic",
    "GIDX":    "gastroenterology diagnostics or therapeutics lab",
    "GIM": "general internal medicine clinic",
    "GRFTH":   "grandfather",
    "GRMTH":   "grandmother",
    "GRNDCHILD":   "grandchild",
    "GRNDDAU": "granddaughter",
    "GRNDSON": "grandson",
    "GRPRN":   "grandparent",
    "GT":  "guarantor",
    "GUADLTM": "guardian ad lidem",
    "GUARD":   "guardian",
    "GYN": "gynecology clinic",
    "HAND":    "hand clinic",
    "HANDIC":  "handicapped dependent",
    "HBRO":    "half-brother",
    "HD":  "hemodialysis unit",
    "HEM": "hematology clinic",
    "HLAB":    "hospital laboratory",
    "HOMEHEALTH":  "home health",
    "HOSP":    "hospital",
    "HPOWATT": "healthcare power of attorney",
    "HRAD":    "radiology unit",
    "HSIB":    "half-sibling",
    "HSIS":    "half-sister",
    "HTN": "hypertension clinic",
    "HU":  "hospital unit",
    "HUSB":    "husband",
    "HUSCS":   "specimen collection site",
    "ICU": "intensive care unit",
    "IEC": "impairment evaluation center",
    "INDIG":   "member of an indigenous people",
    "INFD":    "infectious disease clinic",
    "INJ": "injured plaintiff",
    "INJWKR":  "injured worker",
    "INLAB":   "inpatient laboratory",
    "INPHARM": "inpatient pharmacy",
    "INV": "infertility clinic",
    "JURID":   "jurisdiction location identifier",
    "L":   "pool",
    "LABORATORY":  "laboratory",
    "LOCHFID": "local location identifier",
    "LY":  "layer",
    "LYMPH":   "lympedema clinic",
    "MAUNT":   "maternalaunt",
    "MBL": "medical laboratory",
    "MCOUSN":  "maternalcousin",
    "MGDSF":   "manufactured drug strength form",
    "MGEN":    "medical genetics clinic",
    "MGGRFTH": "maternalgreatgrandfather",
    "MGGRMTH": "maternalgreatgrandmother",
    "MGGRPRN": "maternalgreatgrandparent",
    "MGRFTH":  "maternalgrandfather",
    "MGRMTH":  "maternalgrandmother",
    "MGRPRN":  "maternalgrandparent",
    "MHSP":    "military hospital",
    "MIL": "military",
    "MOBL":    "mobile unit",
    "MT":  "meat",
    "MTH": "mother",
    "MTHINLAW":    "mother-in-law",
    "MU":  "multiplier",
    "MUNCLE":  "maternaluncle",
    "NBOR":    "neighbor",
    "NBRO":    "natural brother",
    "NCCF":    "nursing or custodial care facility",
    "NCCS":    "neurology critical care and stroke unit",
    "NCHILD":  "natural child",
    "NEPH":    "nephrology clinic",
    "NEPHEW":  "nephew",
    "NEUR":    "neurology clinic",
    "NFTH":    "natural father",
    "NFTHF":   "natural father of fetus",
    "NIECE":   "niece",
    "NIENEPH": "niece/nephew",
    "NMTH":    "natural mother",
    "NOK": "next of kin",
    "NPRN":    "natural parent",
    "NS":  "neurosurgery unit",
    "NSIB":    "natural sibling",
    "NSIS":    "natural sister",
    "O":   "operator proficiency",
    "OB":  "obstetrics clinic",
    "OF":  "outpatient facility",
    "OMS": "oral and maxillofacial surgery clinic",
    "ONCL":    "medical oncology clinic",
    "OPH": "opthalmology clinic",
    "OPTC":    "optometry clinic",
    "ORG": "organizational contact",
    "ORTHO":   "orthopedics clinic",
    "OUTLAB":  "outpatient laboratory",
    "OUTPHARM":    "outpatient pharmacy",
    "P":   "patient",
    "PAINCL":  "pain clinic",
    "PATHOLOGIST": "pathologist",
    "PAUNT":   "paternalaunt",
    "PAYOR":   "payor contact",
    "PC":  "primary care clinic",
    "PCOUSN":  "paternalcousin",
    "PEDC":    "pediatrics clinic",
    "PEDCARD": "pediatric cardiology clinic",
    "PEDE":    "pediatric endocrinology clinic",
    "PEDGI":   "pediatric gastroenterology clinic",
    "PEDHEM":  "pediatric hematology clinic",
    "PEDHO":   "pediatric oncology clinic",
    "PEDICU":  "pediatric intensive care unit",
    "PEDID":   "pediatric infectious disease clinic",
    "PEDNEPH": "pediatric nephrology clinic",
    "PEDNICU": "pediatric neonatal intensive care unit",
    "PEDRHEUM":    "pediatric rheumatology clinic",
    "PEDU":    "pediatric unit",
    "PGGRFTH": "paternalgreatgrandfather",
    "PGGRMTH": "paternalgreatgrandmother",
    "PGGRPRN": "paternalgreatgrandparent",
    "PGRFTH":  "paternalgrandfather",
    "PGRMTH":  "paternalgrandmother",
    "PGRPRN":  "paternalgrandparent",
    "PH":  "policy holder",
    "PHARM":   "pharmacy",
    "PHLEBOTOMIST":    "phlebotomist",
    "PHU": "psychiatric hospital unit",
    "PL":  "pleasure",
    "PLS": "plastic surgery clinic",
    "POD": "podiatry clinic",
    "POWATT":  "power of attorney",
    "PRC": "pain rehabilitation center",
    "PREV":    "preventive medicine clinic",
    "PRN": "parent",
    "PRNINLAW":    "parent in-law",
    "PROCTO":  "proctology clinic",
    "PROFF":   "provider's office",
    "PROG":    "program eligible",
    "PROS":    "prosthodontics clinic",
    "PRS": "personal relationship",
    "PSI": "psychology clinic",
    "PSTUD":   "part-time student",
    "PSY": "psychiatry clinic",
    "PSYCHF":  "psychiatric care facility",
    "PT":  "patient",
    "PTRES":   "patient's residence",
    "PUNCLE":  "paternaluncle",
    "Q":   "quality control",
    "R":   "replicate",
    "RADDX":   "radiology diagnostics or therapeutics unit",
    "RADO":    "radiation oncology unit",
    "RC":  "racing",
    "RESPRSN": "responsible party",
    "RETIREE": "retiree",
    "RETMIL":  "retired military",
    "RH":  "rehabilitation hospital",
    "RHAT":    "addiction treatment center",
    "RHEUM":   "rheumatology clinic",
    "RHII":    "intellectual impairment center",
    "RHMAD":   "parents with adjustment difficulties center",
    "RHPI":    "physical impairment center",
    "RHPIH":   "physical impairment - hearing center",
    "RHPIMS":  "physical impairment - motor skills center",
    "RHPIVS":  "physical impairment - visual skills center",
    "RHU": "rehabilitation hospital unit",
    "RHYAD":   "youths with adjustment difficulties center",
    "RNEU":    "neuroradiology unit",
    "ROOM":    "roommate",
    "RTF": "residential treatment facility",
    "SCHOOL":  "school",
    "SCN": "screening",
    "SEE": "seeing",
    "SELF":    "self",
    "SELFINS": "self insured coverage sponsor",
    "SH":  "show",
    "SIB": "sibling",
    "SIBINLAW":    "sibling in-law",
    "SIGOTHR": "significant other",
    "SIS": "sister",
    "SISINLAW":    "sister-in-law",
    "SLEEP":   "sleep disorders unit",
    "SNF": "skilled nursing facility",
    "SNIFF":   "sniffing",
    "SON": "natural son",
    "SONADOPT":    "adopted son",
    "SONC":    "son",
    "SONFOST": "foster son",
    "SONINLAW":    "son in-law",
    "SPMED":   "sports medicine clinic",
    "SPON":    "sponsored dependent",
    "SPOWATT": "special power of attorney",
    "SPS": "spouse",
    "STPBRO":  "stepbrother",
    "STPCHLD": "step child",
    "STPDAU":  "stepdaughter",
    "STPFTH":  "stepfather",
    "STPMTH":  "stepmother",
    "STPPRN":  "step parent",
    "STPSIB":  "step sibling",
    "STPSIS":  "stepsister",
    "STPSON":  "stepson",
    "STUD":    "student",
    "SU":  "surgery clinic",
    "SUBJECT": "self",
    "SURF":    "substance use rehabilitation facility",
    "THIRDPARTY":  "third party",
    "TPA": "third party administrator",
    "TR":  "transplant clinic",
    "TRAVEL":  "travel and geographic medicine clinic",
    "TRB": "tribal member",
    "UMO": "utilization management organization",
    "UNCLE":   "uncle",
    "UPC": "underage protection center",
    "URO": "urology clinic",
    "V":   "verifying",
    "VET": "veteran",
    "VL":  "veal",
    "WARD":    "ward",
    "WIFE":    "wife",
    "WL":  "wool",
    "WND": "wound clinic",
    "WO":  "working",
    "WORK":    "work site",
  };

  var PROBLEM_STATUS_MAP = {
    "55561003": "active",
    "73425007": "inactive",
    "90734009": "chronic",
    "7087005": "intermittent",
    "255227004": "recurrent",
    "413322009": "resolved",
    "415684004": "rule out",
    "410516002": "ruled out"
  };


  // copied from _.invert to avoid making browser users include all of underscore
  var invertKeys = function(obj) {
    var result = {};
    var keys = Object.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };

  var lookupFnGenerator = function(map) {
    return function(key) {
      return map[key] || null;
    };
  };
  var reverseLookupFnGenerator = function(map) {
    return function(key) {
      if (!key) { return null; }
      var invertedMap = invertKeys(map);
      key = key.toLowerCase();
      return invertedMap[key] || null;
    };
  };
  
  
  return {
    gender: lookupFnGenerator(GENDER_MAP),
    reverseGender: reverseLookupFnGenerator(GENDER_MAP),
    maritalStatus: lookupFnGenerator(MARITAL_STATUS_MAP),
    phone: lookupFnGenerator(PHONE_MAP),
    reverseMaritalStatus: reverseLookupFnGenerator(MARITAL_STATUS_MAP),
    religion: lookupFnGenerator(RELIGION_MAP),
    reverseReligion: reverseLookupFnGenerator(RELIGION_MAP),
    raceEthnicity: lookupFnGenerator(RACE_ETHNICITY_MAP),
    reverseRaceEthnicity: reverseLookupFnGenerator(RACE_ETHNICITY_MAP),
    role: lookupFnGenerator(ROLE_MAP),
    reverseRole: reverseLookupFnGenerator(ROLE_MAP),
    problemStatus: lookupFnGenerator(PROBLEM_STATUS_MAP),
    reverseProblemStatus: reverseLookupFnGenerator(PROBLEM_STATUS_MAP)
  };
  
})();
;

/*
 * ...
 */

Core.XML = (function () {
  
  /*
   * A function used to wrap DOM elements in an object so methods can be added
   * to the element object. IE8 does not allow methods to be added directly to
   * DOM objects.
   */
  var wrapElement = function (el) {
    function wrapElementHelper(currentEl) {
      return {
        el: currentEl,
        template: template,
        content: content,
        tag: tag,
        immediateChildTag: immediateChildTag,
        immediateChildrenTags: immediateChildrenTags,
        elsByTag: elsByTag,
        attr: attr,
        boolAttr: boolAttr,
        val: val,
        isEmpty: isEmpty
      };
    }
    
    // el is an array of elements
    if (el.length) {
      var els = [];
      for (var i = 0; i < el.length; i++) {
        els.push(wrapElementHelper(el[i]));
      }
      return els;
    
    // el is a single element
    } else {
      return wrapElementHelper(el);
    }
  };
  
  
  /*
   * Find element by tag name, then attribute value.
   */
  var tagAttrVal = function (el, tag, attr, value) {
    el = el.getElementsByTagName(tag);
    for (var i = 0; i < el.length; i++) {
      if (el[i].getAttribute(attr) === value) {
        return el[i];
      }
    }
  };
  
  
  /*
   * Search for a template ID, and return its parent element.
   * Example:
   *   <templateId root="2.16.840.1.113883.10.20.22.2.17"/>
   * Can be found using:
   *   el = dom.template('2.16.840.1.113883.10.20.22.2.17');
   */
  var template = function (templateId) {
    var el = tagAttrVal(this.el, 'templateId', 'root', templateId);
    if (!el) {
      return emptyEl();
    } else {
      return wrapElement(el.parentNode);
    }
  };

  /*
   * Search for a content tag by "ID", and return it as an element.
   * These are used in the unstructured versions of each section but
   * referenced from the structured version sometimes.
   * Example:
   *   <content ID="UniqueNameReferencedElsewhere"/>
   * Can be found using:
   *   el = dom.content('UniqueNameReferencedElsewhere');
   *
   * We can't use `getElementById` because `ID` (the standard attribute name
   * in this context) is not the same attribute as `id` in XML, so there are no matches
   */
  var content = function (contentId) {
      var el = tagAttrVal(this.el, 'content', 'ID', contentId);
      if (!el) {
        // check the <td> tag too, which isn't really correct but
        // will inevitably be used sometimes because it looks like very
        // normal HTML to put the data directly in a <td>
        el = tagAttrVal(this.el, 'td', 'ID', contentId);
      }
      if (!el) {
        // Ugh, Epic uses really non-standard locations.
        el = tagAttrVal(this.el, 'caption', 'ID', contentId) ||
             tagAttrVal(this.el, 'paragraph', 'ID', contentId) ||
             tagAttrVal(this.el, 'tr', 'ID', contentId) ||
             tagAttrVal(this.el, 'item', 'ID', contentId);
      }

      if (!el) {
        return emptyEl();
      } else {
        return wrapElement(el);
      }
    };
  
  
  /*
   * Search for the first occurrence of an element by tag name.
   */
  var tag = function (tag) {
    var el = this.el.getElementsByTagName(tag)[0];
    if (!el) {
      return emptyEl();
    } else {
      return wrapElement(el);
    }
  };

  /*
   * Like `tag`, except it will only count a tag that is an immediate child of `this`.
   * This is useful for tags like "text" which A. may not be present for a given location
   * in every document and B. have a very different meaning depending on their positioning
   *
   *   <parent>
   *     <target></target>
   *   </parent>
   * vs.
   *   <parent>
   *     <intermediate>
   *       <target></target>
   *     </intermediate>
   *   </parent>
   * parent.immediateChildTag('target') will have a result in the first case but not in the second.
   */
  var immediateChildTag = function (tag) {
    var els = this.el.getElementsByTagName(tag);
    if (!els) { return null; }
    for (var i = 0; i < els.length; i++) {
      if (els[i].parentNode === this.el) {
        return wrapElement(els[i]);
      }
    }
    return emptyEl();
  };

  /**
   * Like 'immediateChildTag', it will return an array of all tags that are
   * immediate children of this.
   *
   * @param tag
   * @returns {*}
   */
  var immediateChildrenTags = function (tag) {
    var els = this.el.getElementsByTagName(tag);
    if (!els) { return null; }
    var tags = [];
    for (var i = 0; i < els.length; i++) {
      if (els[i].parentNode === this.el) {
        tags.push(wrapElement(els[i]));
      }
    }
    return tags;
  };
  
  
  /*
   * Search for all elements by tag name.
   */
  var elsByTag = function (tag) {
    return wrapElement(this.el.getElementsByTagName(tag));
  };


  var unescapeSpecialChars = function(s) {
    if (!s) { return s; }
    return s.replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&amp;/g, '&')
            .replace(/&quot;/g, '"')
            .replace(/&apos;/g, "'");
  };
  
  
  /*
   * Retrieve the element's attribute value. Example:
   *   value = el.attr('displayName');
   *
   * The browser and jsdom return "null" for empty attributes;
   * xmldom (which we now use because it's faster / can be explicitly
   * told to parse malformed XML as XML anyways), return the empty
   * string instead, so we fix that here.
   */
  var attr = function (attrName) {
    if (!this.el) { return null; }
    var attrVal = this.el.getAttribute(attrName);
    if (attrVal) {
      return unescapeSpecialChars(attrVal);
    }
    return null;
  };

  /*
   * Wrapper for attr() for retrieving boolean attributes;
   * a raw call attr() will return Strings, which can be unexpected,
   * since the string 'false' will by truthy
   */
  var boolAttr = function (attrName) {
    var rawAttr = this.attr(attrName);
    if (rawAttr === 'true' || rawAttr === '1') {
      return true;
    }
    return false;
  };

  
  /*
   * Retrieve the element's value. For example, if the element is:
   *   <city>Madison</city>
   * Use:
   *   value = el.tag('city').val();
   *
   * This function also knows how to retrieve the value of <reference> tags,
   * which can store their content in a <content> tag in a totally different
   * part of the document.
   */
  var val = function () {
    if (!this.el) { return null; }
    if (!this.el.childNodes || !this.el.childNodes.length) { return null; }
    var textContent = this.el.textContent;

    // if there's no text value here and the only thing inside is a
    // <reference> tag, see if there's a linked <content> tag we can
    // get something out of
    if (!Core.stripWhitespace(textContent)) {

      var contentId;
      // "no text value" might mean there's just a reference tag
      if (this.el.childNodes.length === 1 &&
          this.el.childNodes[0].tagName === 'reference') {
        contentId = this.el.childNodes[0].getAttribute('value');

      // or maybe a newlines on top/above the reference tag
      } else if (this.el.childNodes.length === 3 &&
          this.el.childNodes[1].tagName === 'reference') {
        contentId = this.el.childNodes[1].getAttribute('value');

      } else {
        return unescapeSpecialChars(textContent);
      }

      if (contentId && contentId[0] === '#') {
        contentId = contentId.slice(1); // get rid of the '#'
        var docRoot = wrapElement(this.el.ownerDocument);
        var contentTag = docRoot.content(contentId);
        return contentTag.val();
      }
    }

    return unescapeSpecialChars(textContent);
  };
  
  
  /*
   * Creates and returns an empty DOM element with tag name "empty":
   *   <empty></empty>
   */
  var emptyEl = function () {
    var el = doc.createElement('empty');
    return wrapElement(el);
  };
  
  
  /*
   * Determines if the element is empty, i.e.:
   *   <empty></empty>
   * This element is created by function `emptyEL`.
   */
  var isEmpty = function () {
    if (this.el.tagName.toLowerCase() === 'empty') {
      return true;
    } else {
      return false;
    }
  };
  
  
  /*
   * Cross-browser XML parsing supporting IE8+ and Node.js.
   */
  var parse = function (data) {
    // XML data must be a string
    if (!data || typeof data !== "string") {
      console.log("BB Error: XML data is not a string");
      return null;
    }
    
    var xml, parser;
    
    // Node
    if (isNode) {
      parser = new (xmldom.DOMParser)();
      xml = parser.parseFromString(data, "text/xml");

    // Browser
    } else {

      // Standard parser
      if (window.DOMParser) {
        parser = new DOMParser();

        xml = parser.parseFromString(data, "text/xml");

        if (xml.getElementsByTagName("parsererror").length) {
          //used to escape special character
          data = data.replace(/&/g,"&amp;");
          xml = parser.parseFromString(data, "text/xml");
        }
        
      // IE
      } else {
        try {
          xml = new ActiveXObject("Microsoft.XMLDOM");
          xml.async = "false";
          xml.loadXML(data);
        } catch (e) {
          console.log("BB ActiveX Exception: Could not parse XML");
        }
      }
    }

    if (!xml || !xml.documentElement || xml.getElementsByTagName("parsererror").length) {
      console.log("BB Error: Could not parse XML");
      return null;
    }
    
    return wrapElement(xml);
  };
  
  
  // Establish the root object, `window` in the browser, or `global` in Node.
  var root = this,
      xmldom,
      isNode = false,
      doc = root.document; // Will be `undefined` if we're in Node

  // Check if we're in Node. If so, pull in `xmldom` so we can simulate the DOM.
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      isNode = true;
      xmldom = require("xmldom");
      doc = new xmldom.DOMImplementation().createDocument();
    }
  }
  
  
  return {
    parse: parse
  };
  
})();
;

/*
 * ...
 */

/* exported Documents */
var Documents = (function () {

    /*
     * ...
     */
    var detect = function (data) {
        if (!data.template) {
            return 'json';
        }

        if (!data.template('2.16.840.1.113883.3.88.11.32.1').isEmpty()) {
            return 'c32';
        } else if (!data.template('2.16.840.1.113883.10.20.22.1.1').isEmpty()) {
            return 'ccda';
        }
    };


    /*
     * Get entries within an element (with tag name 'entry'), adds an `each` function
     */
    var entries = function () {
        var each = function (callback) {
            for (var i = 0; i < this.length; i++) {
                callback(this[i]);
            }
        };

        var els = this.elsByTag('entry');
        els.each = each;
        return els;
    };


    /*
     * Parses an HL7 date in String form and creates a new Date object.
     *
     * TODO: CCDA dates can be in form:
     *   <effectiveTime value="20130703094812"/>
     * ...or:
     *   <effectiveTime>
     *     <low value="19630617120000"/>
     *     <high value="20110207100000"/>
     *   </effectiveTime>
     * For the latter, parseDate will not be given type `String`
     * and will return `null`.
     */
    var parseDate = function (str) {
        if (!str || typeof str !== 'string') {
            return null;
        }

        // Note: months start at 0 (so January is month 0)

        // e.g., value="1999" translates to Jan 1, 1999
        if (str.length === 4) {
            return new Date(str, 0, 1);
        }

        var year = str.substr(0, 4);
        // subtract 1 from the month since they're zero-indexed
        var month = parseInt(str.substr(4, 2), 10) - 1;
        // days are not zero-indexed. If we end up with the day 0 or '',
        // that will be equivalent to the last day of the previous month
        var day = str.substr(6, 2) || 1;

        // check for time info (the presence of at least hours and mins after the date)
        if (str.length >= 12) {
            var hour = str.substr(8, 2);
            var min = str.substr(10, 2);
            var secs = str.substr(12, 2);

            // check for timezone info (the presence of chars after the seconds place)
            if (str.length > 14) {
                // _utcOffsetFromString will return 0 if there's no utc offset found.
                var utcOffset = _utcOffsetFromString(str.substr(14));
                // We subtract that offset from the local time to get back to UTC
                // (e.g., if we're -480 mins behind UTC, we add 480 mins to get back to UTC)
                min = _toInt(min) - utcOffset;
            }

            var date = new Date(Date.UTC(year, month, day, hour, min, secs));
            // This flag lets us output datetime-precision in our JSON even if the time happens
            // to translate to midnight local time. If we clone the date object, it is not
            // guaranteed to survive.
            date._parsedWithTimeData = true;
            return date;
        }

        return new Date(year, month, day);
    };

    // These regexes and the two functions below are copied from moment.js
    // http://momentjs.com/
    // https://github.com/moment/moment/blob/develop/LICENSE
    var parseTimezoneChunker = /([\+\-]|\d\d)/gi;
    var parseTokenTimezone = /Z|[\+\-]\d\d:?\d\d/gi; // +00:00 -00:00 +0000 -0000 or Z
    function _utcOffsetFromString(string) {
        string = string || '';
        var possibleTzMatches = (string.match(parseTokenTimezone) || []),
            tzChunk = possibleTzMatches[possibleTzMatches.length - 1] || [],
            parts = (tzChunk + '').match(parseTimezoneChunker) || ['-', 0, 0],
            minutes = +(parts[1] * 60) + _toInt(parts[2]);

        return parts[0] === '+' ? minutes : -minutes;
    }

    function _toInt(argumentForCoercion) {
        var coercedNumber = +argumentForCoercion,
            value = 0;

        if (coercedNumber !== 0 && isFinite(coercedNumber)) {
            if (coercedNumber >= 0) {
                value = Math.floor(coercedNumber);
            } else {
                value = Math.ceil(coercedNumber);
            }
        }

        return value;
    }


    /*
     * Parses an HL7 name (prefix / given [] / family)
     */
    var parseName = function (nameEl) {
        var prefix = nameEl.tag('prefix').val();

        var els = nameEl.elsByTag('given');
        var given = [];
        for (var i = 0; i < els.length; i++) {
            var val = els[i].val();
            if (val) {
                given.push(val);
            }
        }

        var family = nameEl.tag('family').val();

        var suffix = nameEl.tag('suffix').val();

        return {
            prefix: prefix,
            given: given,
            family: family,
            suffix: suffix
        };
    };


    /*
     * Parses an HL7 address (streetAddressLine [], city, state, postalCode, country)
     */
    var parseAddress = function (addrEl) {
        var els = addrEl.elsByTag('streetAddressLine');
        var street = [];

        for (var i = 0; i < els.length; i++) {
            var val = els[i].val();
            if (val) {
                street.push(val);
            }
        }

        var city = addrEl.tag('city').val(),
            state = addrEl.tag('state').val(),
            zip = addrEl.tag('postalCode').val(),
            country = addrEl.tag('country').val();

        return {
            street: street,
            city: city,
            state: state,
            zip: zip,
            country: country
        };
    };

    var parseIds = function (idEl) {
        var ids = idEl.map(function (tag) {
            return {
                extension: tag.attr('extension'),
                root: tag.attr('root'),
                assigningAuthorityName: tag.attr('assigningAuthorityName')
            };
        });
        return ids;
    };

    var parsePhones = function (phoneEl) {
        var phones = phoneEl.map(function (tag) {
            return {
                type: Core.Codes.phone(tag.attr('use')),
                number: tag.attr('value')
            };
        });
        return phones;
    };

    // Node-specific code for unit tests
    if (typeof exports !== 'undefined') {
        if (typeof module !== 'undefined' && module.exports) {
            module.exports = {
                parseDate: parseDate
            };
        }
    }


    return {
        detect: detect,
        entries: entries,
        parseDate: parseDate,
        parseName: parseName,
        parseAddress: parseAddress,
        parseIds: parseIds,
        parsePhones: parsePhones
    };

})();
;

/*
 * ...
 */

Documents.C32 = (function () {
  
  /*
   * Preprocesses the C32 document
   */
  var process = function (c32) {
    c32.section = section;
    return c32;
  };
  
  
  /*
   * Finds the section of a C32 document
   *
   * Usually we check first for the HITSP section ID and then for the HL7-CCD ID.
   */
  var section = function (name) {
    var el, entries = Documents.entries;
    
    switch (name) {
      case 'document':
        return this.template('2.16.840.1.113883.3.88.11.32.1');
      case 'allergies':
        el = this.template('2.16.840.1.113883.3.88.11.83.102');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.2');
        }
        el.entries = entries;
        return el;
      case 'demographics':
        return this.template('2.16.840.1.113883.3.88.11.32.1');
      case 'encounters':
        el = this.template('2.16.840.1.113883.3.88.11.83.127');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.3');
        }
        el.entries = entries;
        return el;
      case 'immunizations':
        el = this.template('2.16.840.1.113883.3.88.11.83.117');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.6');
        }
        el.entries = entries;
        return el;
      case 'results':
        el = this.template('2.16.840.1.113883.3.88.11.83.122');
        el.entries = entries;
        return el;
      case 'medications':
        el = this.template('2.16.840.1.113883.3.88.11.83.112');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.8');
        }
        el.entries = entries;
        return el;
      case 'problems':
        el = this.template('2.16.840.1.113883.3.88.11.83.103');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.11');
        }
        el.entries = entries;
        return el;
      case 'procedures':
        el = this.template('2.16.840.1.113883.3.88.11.83.108');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.12');
        }
        el.entries = entries;
        return el;
      case 'vitals':
        el = this.template('2.16.840.1.113883.3.88.11.83.119');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.1.16');
        }
        el.entries = entries;
        return el;
    }
    
    return null;
  };
  
  
  return {
    process: process,
    section: section
  };
  
})();
;

/*
 * ...
 */

Documents.CCDA = (function () {
  
  /*
   * Preprocesses the CCDA document
   */
  var process = function (ccda) {
    ccda.section = section;
    return ccda;
  };
  
  
  /*
   * Finds the section of a CCDA document
   */
  var section = function (name) {
    var el, entries = Documents.entries;
    
    switch (name) {
      case 'document':
        return this.template('2.16.840.1.113883.10.20.22.1.1');
      case 'allergies':
        el = this.template('2.16.840.1.113883.10.20.22.2.6.1');
        el.entries = entries;
        return el;
      case 'care_plan':
        el = this.template('2.16.840.1.113883.10.20.22.2.10');
        el.entries = entries;
        return el;
      case 'chief_complaint':
        el = this.template('2.16.840.1.113883.10.20.22.2.13');
        if (el.isEmpty()) {
          el = this.template('1.3.6.1.4.1.19376.1.5.3.1.1.13.2.1');
        }
        // no entries in Chief Complaint
        return el;
      case 'demographics':
        return this.template('2.16.840.1.113883.10.20.22.1.1');
      case 'encounters':
        el = this.template('2.16.840.1.113883.10.20.22.2.22');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.22.1');
        }
        el.entries = entries;
        return el;
      case 'functional_statuses':
        el = this.template('2.16.840.1.113883.10.20.22.2.14');
        el.entries = entries;
        return el;
      case 'immunizations':
        el = this.template('2.16.840.1.113883.10.20.22.2.2.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.2');
        }
        el.entries = entries;
        return el;
      case 'instructions':
        el = this.template('2.16.840.1.113883.10.20.22.2.45');
        el.entries = entries;
        return el;
      case 'results':
        el = this.template('2.16.840.1.113883.10.20.22.2.3.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.3');
        }
        el.entries = entries;
        return el;
      case 'medications':
        el = this.template('2.16.840.1.113883.10.20.22.2.1.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.1');
        }
        el.entries = entries;
        return el;
      case 'problems':
        el = this.template('2.16.840.1.113883.10.20.22.2.5.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.5');
        }
        el.entries = entries;
        return el;
      case 'procedures':
        el = this.template('2.16.840.1.113883.10.20.22.2.7.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.7');
        }
        el.entries = entries;
        return el;
      case 'social_history':
        el = this.template('2.16.840.1.113883.10.20.22.2.17');
        el.entries = entries;
        return el;
      case 'vitals':
        el = this.template('2.16.840.1.113883.10.20.22.2.4.1');
        if (el.isEmpty()) {
          el = this.template('2.16.840.1.113883.10.20.22.2.4');
        }
        el.entries = entries;
        return el;
    }
    
    return null;
  };
  
  
  return {
    process: process,
    section: section
  };
  
})();
;

/*
 * ...
 */

/* exported Generators */
var Generators = (function () {
  
  var method = function () {};

  /* Import ejs if we're in Node. Then setup custom formatting filters
   */
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      ejs = require("ejs");
    }
  }

  if (typeof ejs !== 'undefined') {
    /* Filters are automatically available to ejs to be used like "... | hl7Date"
     * Helpers are functions that we'll manually pass in to ejs.
     * The intended distinction is that a helper gets called with regular function-call syntax
     */
    var pad = function(number) {
      if (number < 10) {
        return '0' + number;
      }
      return String(number);
    };

    ejs.filters.hl7Date = function(obj) {
      try {
          if (obj === null || obj === undefined) { return 'nullFlavor="UNK"'; }
          var date = new Date(obj);
          if (isNaN(date.getTime())) { return obj; }

          var dateStr = null;
          if (date.getHours() || date.getMinutes() || date.getSeconds()) {
            // If there's a meaningful time, output a UTC datetime
            dateStr = date.getUTCFullYear() +
              pad( date.getUTCMonth() + 1 ) +
              pad( date.getUTCDate() );
            var timeStr = pad( date.getUTCHours() ) +
              pad( date.getUTCMinutes() ) +
              pad ( date.getUTCSeconds() ) +
              "+0000";
            return 'value="' + dateStr + timeStr + '"';
           
          } else {
            // If there's no time, don't apply timezone tranformations: just output a date
            dateStr = String(date.getFullYear()) +
              pad( date.getMonth() + 1 ) +
              pad( date.getDate() );
            return 'value="' + dateStr + '"';
          }

      } catch (e) {
          return obj;
      }
    };

    var escapeSpecialChars = function(s) {
      return s.replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/&/g, '&amp;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&apos;');
    };

    ejs.filters.hl7Code = function(obj) {
      if (!obj) { return ''; }

      var tag = '';
      var name = obj.name || '';
      if (obj.name) { tag += 'displayName="'+escapeSpecialChars(name)+'"'; }

      if (obj.code) {
        tag += ' code="'+obj.code+'"';
        if (obj.code_system) { tag += ' codeSystem="'+escapeSpecialChars(obj.code_system)+'"'; }
        if (obj.code_system_name) { tag += ' codeSystemName="' +
                                        escapeSpecialChars(obj.code_system_name)+'"'; }
      } else {
        tag += ' nullFlavor="UNK"';
      }

      if (!obj.name && ! obj.code) {
        return 'nullFlavor="UNK"';
      }
      
      return tag;
    };

    ejs.filters.emptyStringIfFalsy = function(obj) {
      if (!obj) { return ''; }
      return obj;
    };

    if (!ejs.helpers) ejs.helpers = {};
    ejs.helpers.simpleTag = function(tagName, value) {
      if (value) {
        return "<"+tagName+">"+value+"</"+tagName+">";
      } else {
        return "<"+tagName+" nullFlavor=\"UNK\" />";
      }
    };

    ejs.helpers.addressTags = function(addressDict) {
      if (!addressDict) {
        return '<streetAddressLine nullFlavor="NI" />\n' +
                '<city nullFlavor="NI" />\n' +
                '<state nullFlavor="NI" />\n' +
                '<postalCode nullFlavor="NI" />\n' +
                '<country nullFlavor="NI" />\n';
      }
      
      var tags = '';
      if (!addressDict.street.length) {
        tags += ejs.helpers.simpleTag('streetAddressLine', null) + '\n';
      } else {
        for (var i=0; i<addressDict.street.length; i++) {
          tags += ejs.helpers.simpleTag('streetAddressLine', addressDict.street[i]) + '\n';
        }
      }
      tags += ejs.helpers.simpleTag('city', addressDict.city) + '\n';
      tags += ejs.helpers.simpleTag('state', addressDict.state) + '\n';
      tags += ejs.helpers.simpleTag('postalCode', addressDict.zip) + '\n';
      tags += ejs.helpers.simpleTag('country', addressDict.country) + '\n';
      return tags;
    };

    ejs.helpers.nameTags = function(nameDict) {
      if (!nameDict) {
        return '<given nullFlavor="NI" />\n' +
                '<family nullFlavor="NI" />\n';
      }

      var tags = '';
      if (nameDict.prefix) {
        tags += ejs.helpers.simpleTag('prefix', nameDict.prefix) + '\n';
      }
      if (!nameDict.given.length) {
        tags += ejs.helpers.simpleTag('given', null) + '\n';
      } else {
        for (var i=0; i<nameDict.given.length; i++) {
          tags += ejs.helpers.simpleTag('given', nameDict.given[i]) + '\n';
        }
      }
      tags += ejs.helpers.simpleTag('family', nameDict.family) + '\n';
      if (nameDict.suffix) {
        tags += ejs.helpers.simpleTag('suffix', nameDict.suffix) + '\n';
      }
      return tags;
    };

  }
  
  return {
    method: method
  };
  
})();
;

/*
 * ...
 */

Generators.C32 = (function () {
  
  /*
   * Generates a C32 document
   */
  var run = function (json, template, testingMode) {
    /* jshint unused: false */ // only until this stub is actually implemented
    console.log("C32 generation is not implemented yet");
    return null;
  };
  
  return {
    run: run
  };
  
})();
;

/*
 * ...
 */

Generators.CCDA = (function () {
  
  /*
   * Generates a CCDA document
   * A lot of the EJS setup happens in generators.js
   *
   * If `testingMode` is true, we'll set the "now" variable to a specific,
   * fixed time, so that the expected XML doesn't change across runs
   */
  var run = function (json, template, testingMode) {
    if (!ejs) {
      console.log("The BB.js Generator (JSON->XML) requires the EJS template package. " +
                  "Install it in Node or include it before this package in the browser.");
      return null;
    }
    if (!template) {
      console.log("Please provide a template EJS file for the Generator to use. " +
                  "Load it via fs.readFileSync in Node or XHR in the browser.");
      return null;
    }

    // `now` is actually now, unless we're running this for a test,
    // in which case it's always Jan 1, 2000 at 12PM UTC
    var now = (testingMode) ?
      new Date('2000-01-01T12:00:00Z') : new Date();

    var ccda = ejs.render(template, {
      filename: 'ccda.xml',
      bb: json,
      now: now,
      tagHelpers: ejs.helpers,
      codes: Core.Codes
    });
    return ccda;
  };
  
  return {
    run: run
  };
  
})();
;

/*
 * ...
 */

/* exported Parsers */
var Parsers = (function () {
  
  var method = function () {};
  
  return {
    method: method
  };
  
})();
;

/*
 * Parser for the C32 document
 */

Parsers.C32 = (function () {
  
  var run = function (c32) {
    var data = {};
    
    data.document              = Parsers.C32.document(c32);
    data.allergies             = Parsers.C32.allergies(c32);
    data.demographics          = Parsers.C32.demographics(c32);
    data.encounters            = Parsers.C32.encounters(c32);
    data.immunizations         = Parsers.C32.immunizations(c32).administered;
    data.immunization_declines = Parsers.C32.immunizations(c32).declined;
    data.results               = Parsers.C32.results(c32);
    data.medications           = Parsers.C32.medications(c32);
    data.problems              = Parsers.C32.problems(c32);
    data.procedures            = Parsers.C32.procedures(c32);
    data.vitals                = Parsers.C32.vitals(c32);
    
    data.json                       = Core.json;
    data.document.json              = Core.json;
    data.allergies.json             = Core.json;
    data.demographics.json          = Core.json;
    data.encounters.json            = Core.json;
    data.immunizations.json         = Core.json;
    data.immunization_declines.json = Core.json;
    data.results.json               = Core.json;
    data.medications.json           = Core.json;
    data.problems.json              = Core.json;
    data.procedures.json            = Core.json;
    data.vitals.json                = Core.json;

    // Sections that are in CCDA but not C32... we want to keep the API
    // consistent, even if the entries are always null
    data.smoking_status = {
      date: null,
      name: null,
      code: null,
      code_system: null,
      code_system_name: null
    };
    data.smoking_status.json = Core.json;
    
    data.chief_complaint = {
      text: null
    };
    data.chief_complaint.json = Core.json;

    data.care_plan = [];
    data.care_plan.json = Core.json;

    data.instructions = [];
    data.instructions.json = Core.json;

    data.functional_statuses = [];
    data.functional_statuses.json = Core.json;
    
    return data;
  };

  return {
    run: run
  };
  
})();
;

/*
 * Parser for the C32 document section
 */

Parsers.C32.document = function (c32) {

    var parseDate = Documents.parseDate;
    var parseName = Documents.parseName;
    var parseAddress = Documents.parseAddress;
    var parseIds = Documents.parseIds;
    var parsePhones = Documents.parsePhones;

    var data = {}, el;

    var doc = c32.section('document');

    var date = parseDate(doc.tag('effectiveTime').attr('value'));
    var title = Core.stripWhitespace(doc.tag('title').val());

    var author = doc.tag('author');

    el = author.tag('assignedPerson').tag('name');
    var name_dict = parseName(el);

    /**
     * Just for uniformity with CCDA
     * @type {null}
     */
    var author_npi = null;

    // Sometimes C32s include names that are just like <name>String</name>
    // and we still want to get something out in that case
    if (!name_dict.prefix && !name_dict.given.length && !name_dict.family) {
        name_dict.family = el.val();
    }

    el = author.tag('addr');
    var address_dict = parseAddress(el);

    el = author.tag('assignedAuthor').immediateChildrenTags('telecom');
    var author_phones = parsePhones(el);

    var documentation_of_list = [];
    var performers = doc.tag('documentationOf').elsByTag('performer');
    for (var i = 0; i < performers.length; i++) {
        el = performers[i].tag('assignedPerson').tag('name');
        var performer_name_dict = parseName(el);
        var performer_phones = parsePhones(performers[i].tag('assignedEntity').immediateChildrenTags('telecom'));

        console.log('C32');
        console.log(performers[i].tag('telecom').attr('value'));
        console.log(performers[i].tag('assignedEntity').immediateChildrenTags('telecom'));

        var performer_addr = parseAddress(el.tag('addr'));
        var npi = performers[i].tag('assignedEntity').tag('id').attr('extension');

        documentation_of_list.push({
            npi: npi,
            name: performer_name_dict,
            phones: performer_phones,
            address: performer_addr
        });
    }

    el = doc.tag('legalAuthenticator');

    var legal_date = parseDate(el.tag('time').attr('value'));
    var legal_assigned_person = parseName(el.tag('assignedPerson').tag('name'));
    var legal_org_address = parseAddress(el.tag('representedOrganization').tag('addr'));

    var el2 = el.tag('assignedEntity').immediateChildrenTags('id');
    var legal_ids = parseIds(el2);

    var idEl = el.tag('representedOrganization').immediateChildrenTags('id');
    var legal_org_ids = parseIds(idEl);

    var legal_org_name = el.tag('representedOrganization').tag('name').val();

    var phonesEl = el.tag('representedOrganization').immediateChildrenTags('telecom');
    var legal_org_phones = parsePhones(phonesEl);

    el = doc.tag('encompassingEncounter');
    var location_name = Core.stripWhitespace(el.tag('name').val());
    var location_addr_dict = parseAddress(el.tag('addr'));

    var encounter_date = null;
    el = el.tag('effectiveTime');
    if (!el.isEmpty()) {
        encounter_date = parseDate(el.attr('value'));
    }

    var custodianName = doc.tag('custodian').tag('assignedCustodian').tag('representedCustodianOrganization').tag('name').val();

    data = {
        custodian: {
            name: custodianName
        },
        date: date,
        title: title,
        author: {
            npi: author_npi,
            name: name_dict,
            address: address_dict,
            phones: author_phones
        },
        documentation_of: documentation_of_list,
        legal_authenticator: {
            date: legal_date,
            ids: legal_ids,
            assigned_person: legal_assigned_person,
            representedOrganization: {
                ids: legal_org_ids,
                name: legal_org_name,
                phones: legal_org_phones,
                address: legal_org_address
            }
        },
        location: {
            name: location_name,
            address: location_addr_dict,
            encounter_date: encounter_date
        }
    };

    return data;
};
;

/*
 * Parser for the C32 allergies section
 */

Parsers.C32.allergies = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var allergies = c32.section('allergies');
  
  allergies.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var start_date = parseDate(el.tag('low').attr('value')),
        end_date = parseDate(el.tag('high').attr('value'));
    
    el = entry.template('2.16.840.1.113883.3.88.11.83.6').tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');
    
    // value => reaction_type
    el = entry.template('2.16.840.1.113883.3.88.11.83.6').tag('value');
    var reaction_type_name = el.attr('displayName'),
        reaction_type_code = el.attr('code'),
        reaction_type_code_system = el.attr('codeSystem'),
        reaction_type_code_system_name = el.attr('codeSystemName');
    
    // reaction
    el = entry.template('2.16.840.1.113883.10.20.1.54').tag('value');
    var reaction_name = el.attr('displayName'),
        reaction_code = el.attr('code'),
        reaction_code_system = el.attr('codeSystem');
    
    // an irregularity seen in some c32s
    if (!reaction_name) {
      el = entry.template('2.16.840.1.113883.10.20.1.54').tag('text');
      if (!el.isEmpty()) {
        reaction_name = Core.stripWhitespace(el.val());
      }
    }

    // severity
    el = entry.template('2.16.840.1.113883.10.20.1.55').tag('value');
    var severity = el.attr('displayName');
    
    // participant => allergen
    el = entry.tag('participant').tag('code');
    var allergen_name = el.attr('displayName'),
        allergen_code = el.attr('code'),
        allergen_code_system = el.attr('codeSystem'),
        allergen_code_system_name = el.attr('codeSystemName');

    // another irregularity seen in some c32s
    if (!allergen_name) {
      el = entry.tag('participant').tag('name');
      if (!el.isEmpty()) {
        allergen_name = el.val();
      }
    }
    if (!allergen_name) {
      el = entry.template('2.16.840.1.113883.3.88.11.83.6').tag('originalText');
      if (!el.isEmpty()) {
        allergen_name = Core.stripWhitespace(el.val());
      }
    }
    
    // status
    el = entry.template('2.16.840.1.113883.10.20.1.39').tag('value');
    var status = el.attr('displayName');
    
    data.push({
      date_range: {
        start: start_date,
        end: end_date
      },
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      status: status,
      severity: severity,
      reaction: {
        name: reaction_name,
        code: reaction_code,
        code_system: reaction_code_system
      },
      reaction_type: {
        name: reaction_type_name,
        code: reaction_type_code,
        code_system: reaction_type_code_system,
        code_system_name: reaction_type_code_system_name
      },
      allergen: {
        name: allergen_name,
        code: allergen_code,
        code_system: allergen_code_system,
        code_system_name: allergen_code_system_name
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 demographics section
 */

Parsers.C32.demographics = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var parsePhones = Documents.parsePhones;
  var parseIds = Documents.parseIds;
  var data = {}, el;
  
  var demographics = c32.section('demographics');
  
  var patient = demographics.tag('patientRole');
  el = patient.tag('patient').tag('name');
  var patient_name_dict = parseName(el);

  var mrn_number = patient.tag('id').attr('extension');

  el = patient.tag('patient');
  var dob = parseDate(el.tag('birthTime').attr('value')),
      gender = Core.Codes.gender(el.tag('administrativeGenderCode').attr('code')),

      marital_status = Core.Codes.maritalStatus(el.tag('maritalStatusCode').attr('code'));
  
  el = patient.tag('addr');
  var patient_address_dict = parseAddress(el);

  el = patient.immediateChildrenTags('telecom');

  var phones = parsePhones(el);

  var email = null;

    var language = patient.tag('languageCommunication').tag('languageCode').attr('code'),
      race = patient.tag('raceCode').attr('displayName'),
      ethnicity = patient.tag('ethnicGroupCode').attr('displayName'),
      religion = patient.tag('religiousAffiliationCode').attr('displayName');
  
  el = patient.tag('birthplace');
  var birthplace_dict = parseAddress(el);
  
  el = patient.tag('guardian');
  var guardian_relationship = el.tag('code').attr('displayName'),
    guardian_relationship_code = el.tag('code').attr('code'),
      guardian_home = el.tag('telecom').attr('value');
  
  el = el.tag('guardianPerson').tag('name');
  var guardian_name_dict = parseName(el);
  
  el = patient.tag('guardian').tag('addr');
  var guardian_address_dict = parseAddress(el);
  
  el = patient.tag('providerOrganization');
  var provider_organization = el.tag('name').val(),
      provider_phones = parsePhones(el.immediateChildrenTags('telecom')),
      provider_address_dict = parseAddress(el.tag('addr')),
      provider_ids = parseIds(el.immediateChildrenTags('id'));


  el = demographics.immediateChildrenTags('participant');

  var patient_contacts = el.map(function(tag) {
    tag = tag.tag('associatedEntity');

    return {
      relationship: Core.Codes.role(tag.attr('classCode')),
      relationship_code: {
        code: tag.tag('code').attr('code'),
        displayName: tag.tag('code').attr('displayName'),
      },
      phones: parsePhones(tag.immediateChildrenTags('telecom')),
      name: parseName(tag.tag('associatedPerson').tag('name'))
    }
  });

  data = {
    name: patient_name_dict,
    dob: dob,
    gender: gender,
    mrn_number: mrn_number,
    marital_status: marital_status,
    address: patient_address_dict,
    phones: phones,
    email: email,
    language: language,
    race: race,
    ethnicity: ethnicity,
    religion: religion,
    birthplace: {
      state: birthplace_dict.state,
      zip: birthplace_dict.zip,
      country: birthplace_dict.country
    },
    guardian: {
      name: {
        given: guardian_name_dict.given,
        family: guardian_name_dict.family
      },
      relationship: guardian_relationship,
      relationship_code: guardian_relationship_code,
      address: guardian_address_dict,
      phone: {
        home: guardian_home
      }
    },
    patient_contacts: patient_contacts,
    provider: {
      ids: provider_ids,
      organization: provider_organization,
      phones: provider_phones,
      address: provider_address_dict
    }
  };
  
  return data;
};
;

/*
 * Parser for the C32 encounters section
 */

Parsers.C32.encounters = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var parseIds = Documents.parseIds;

  var data = [], el;
  
  var encounters = c32.section('encounters');
  
  encounters.entries().each(function(entry) {
    
    var date = parseDate(entry.tag('effectiveTime').attr('value'));
    if (!date) {
      date = parseDate(entry.tag('effectiveTime').tag('low').attr('value'));
    }
    
    el = entry.tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName'),
        code_system_version = el.attr('codeSystemVersion');
    
    // translation
    el = entry.tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
    // performer
    el = entry.tag('performer');
    var performer_name = el.tag('name').val(),
        performer_code = el.attr('code'),
        performer_code_system = el.attr('codeSystem'),
        performer_code_system_name = el.attr('codeSystemName'),
        performer_ids = parseIds(entry.tag('performer').tag('assignedEntity').immediateChildrenTags('id'));
    
    // participant => location
    el = entry.tag('participant');
    var organization = el.tag('name').val(),
        location_dict = parseAddress(el);
    location_dict.organization = organization;

    // findings
    var findings = [];
    var findingEls = entry.elsByTag('entryRelationship');
    for (var i = 0; i < findingEls.length; i++) {
      el = findingEls[i].tag('value');
      findings.push({
        name: el.attr('displayName'),
        code: el.attr('code'),
        code_system: el.attr('codeSystem')
      });
    }
    
    data.push({
      date: date,
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      code_system_version: code_system_version,
      findings: findings,
      translation: {
        name: translation_name,
        code: translation_code,
        code_system: translation_code_system,
        code_system_name: translation_code_system_name
      },
      performer: {
        ids: performer_ids,
        name: performer_name,
        code: performer_code,
        code_system: performer_code_system,
        code_system_name: performer_code_system_name
      },
      location: location_dict
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 immunizations section
 */

Parsers.C32.immunizations = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var administeredData = [], declinedData = [], el, product;
  
  var immunizations = c32.section('immunizations');
  
  immunizations.entries().each(function(entry) {
    
    // date
    el = entry.tag('effectiveTime');
    var date = parseDate(el.attr('value'));
    if (!date) {
      date = parseDate(el.tag('low').attr('value'));
    }

    // if 'declined' is true, this is a record that this vaccine WASN'T administered
    el = entry.tag('substanceAdministration');
    var declined = el.boolAttr('negationInd');

    // product
    product = entry.template('2.16.840.1.113883.10.20.1.53');
    el = product.tag('code');
    var product_name = el.attr('displayName'),
        product_code = el.attr('code'),
        product_code_system = el.attr('codeSystem'),
        product_code_system_name = el.attr('codeSystemName');

    // translation
    el = product.tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');

    // misc product details
    el = product.tag('lotNumberText');
    var lot_number = el.val();

    el = product.tag('manufacturerOrganization');
    var manufacturer_name = el.tag('name').val();
    
    // route
    el = entry.tag('routeCode');
    var route_name = el.attr('displayName'),
        route_code = el.attr('code'),
        route_code_system = el.attr('codeSystem'),
        route_code_system_name = el.attr('codeSystemName');
    
    // instructions
    el = entry.template('2.16.840.1.113883.10.20.1.49');
    var instructions_text = Core.stripWhitespace(el.tag('text').val());
    el = el.tag('code');
    var education_name = el.attr('displayName'),
        education_code = el.attr('code'),
        education_code_system = el.attr('codeSystem');

    // dose
    el = entry.tag('doseQuantity');
    var dose_value = el.attr('value'),
        dose_unit = el.attr('unit');
    
    var data = (declined) ? declinedData : administeredData;
    data.push({
      date: date,
      product: {
        name: product_name,
        code: product_code,
        code_system: product_code_system,
        code_system_name: product_code_system_name,
        translation: {
          name: translation_name,
          code: translation_code,
          code_system: translation_code_system,
          code_system_name: translation_code_system_name
        },
        lot_number: lot_number,
        manufacturer_name: manufacturer_name
      },
      dose_quantity: {
        value: dose_value,
        unit: dose_unit
      },
      route: {
        name: route_name,
        code: route_code,
        code_system: route_code_system,
        code_system_name: route_code_system_name
      },
      instructions: instructions_text,
      education_type: {
        name: education_name,
        code: education_code,
        code_system: education_code_system
      }
    });
  });
  
  return {
    administered: administeredData,
    declined: declinedData
  };
};
;

/*
 * Parser for the C32 results (labs) section
 */

Parsers.C32.results = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var results = c32.section('results');
  
  results.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var panel_date = parseDate(entry.tag('effectiveTime').attr('value'));
    if (!panel_date) {
      panel_date = parseDate(entry.tag('effectiveTime').tag('low').attr('value'));
    }
    
    // panel
    el = entry.tag('code');
    var panel_name = el.attr('displayName'),
        panel_code = el.attr('code'),
        panel_code_system = el.attr('codeSystem'),
        panel_code_system_name = el.attr('codeSystemName');
    
    var observation;
    var tests = entry.elsByTag('observation');
    var tests_data = [];
    
    for (var i = 0; i < tests.length; i++) {
      observation = tests[i];
      
      // sometimes results organizers contain non-results. we only want tests
      if (observation.template('2.16.840.1.113883.10.20.1.31').val()) {
        var date = parseDate(observation.tag('effectiveTime').attr('value'));
        
        el = observation.tag('code');
        var name = el.attr('displayName'),
            code = el.attr('code'),
            code_system = el.attr('codeSystem'),
            code_system_name = el.attr('codeSystemName');

        if (!name) {
          name = Core.stripWhitespace(observation.tag('text').val());
        }
    
        el = observation.tag('translation');
        var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
        el = observation.tag('value');
        var value = el.attr('value'),
            unit = el.attr('unit');
        // We could look for xsi:type="PQ" (physical quantity) but it seems better
        // not to trust that that field has been used correctly...
        if (value && !isNaN(parseFloat(value))) {
          value = parseFloat(value);
        }
        if (!value) {
          value = el.val(); // look for free-text values
        }
    
        el = observation.tag('referenceRange');
        var reference_range_text = Core.stripWhitespace(el.tag('observationRange').tag('text').val()),
            reference_range_low_unit = el.tag('observationRange').tag('low').attr('unit'),
            reference_range_low_value = el.tag('observationRange').tag('low').attr('value'),
            reference_range_high_unit = el.tag('observationRange').tag('high').attr('unit'),
            reference_range_high_value = el.tag('observationRange').tag('high').attr('value');
        
        tests_data.push({
          date: date,
          name: name,
          value: value,
          unit: unit,
          code: code,
          code_system: code_system,
          code_system_name: code_system_name,
          translation: {
            name: translation_name,
            code: translation_code,
            code_system: translation_code_system,
            code_system_name: translation_code_system_name
          },
          reference_range: {
            text: reference_range_text,
            low_unit: reference_range_low_unit,
            low_value: reference_range_low_value,
            high_unit: reference_range_high_unit,
            high_value: reference_range_high_value,
          }
        });
      }
    }
    
    data.push({
      name: panel_name,
      code: panel_code,
      code_system: panel_code_system,
      code_system_name: panel_code_system_name,
      date: panel_date,
      tests: tests_data
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 medications section
 */

Parsers.C32.medications = function (c32) {
  
  var parseDate = Documents.parseDate;
  var data = [], el;
  
  var medications = c32.section('medications');
  
  medications.entries().each(function(entry) {

    var reference = entry.tag('reference').attr('value');
    var referenceTitle = entry.tag('text').val();

    if (reference != null) {
      var referenceSig = c32.content('sig-' + reference.split('-')[1]).val();
    }

    var text = null;
    el = entry.tag('substanceAdministration').immediateChildTag('text');
    if (!el.isEmpty()) {
      // technically C32s don't use this, but C83s (another CCD) do,
      // and CCDAs do, so we may see it anyways
      text = Core.stripWhitespace(el.val());
    }

    var effectiveTimes = entry.elsByTag('effectiveTime');

    el = effectiveTimes[0]; // the first effectiveTime is the med start date
    var start_date = null, end_date = null;
    if (el) {
      start_date = parseDate(el.tag('low').attr('value'));
      end_date = parseDate(el.tag('high').attr('value'));
    }

    // the second effectiveTime might the schedule period or it might just
    // be a random effectiveTime from further in the entry... xsi:type should tell us
    el = effectiveTimes[1];
    var schedule_type = null, schedule_period_value = null, schedule_period_unit = null;
    if (el && el.attr('xsi:type') === 'PIVL_TS') {
      var institutionSpecified = el.attr('institutionSpecified');
      if (institutionSpecified === 'true') {
        schedule_type= 'frequency';
      } else if (institutionSpecified === 'false') {
        schedule_type = 'interval';
      }

      el = el.tag('period');
      schedule_period_value = el.attr('value');
      schedule_period_unit = el.attr('unit');
    }
    
    el = entry.tag('manufacturedProduct').tag('code');
    var product_name = el.attr('displayName'),
        product_code = el.attr('code'),
        product_code_system = el.attr('codeSystem');

    var product_original_text = null;
    el = entry.tag('manufacturedProduct').tag('originalText');
    if (!el.isEmpty()) {
      product_original_text = Core.stripWhitespace(el.val());
    }
    // if we don't have a product name yet, try the originalText version
    if (!product_name && product_original_text) {
      product_name = product_original_text;
    }

    // irregularity in some c32s
    if (!product_name) {
      el = entry.tag('manufacturedProduct').tag('name');
      if (!el.isEmpty()) {
        product_name = Core.stripWhitespace(el.val());
      }
    }
    
    el = entry.tag('manufacturedProduct').tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
    el = entry.tag('doseQuantity');
    var dose_value = el.attr('value'),
        dose_unit = el.attr('unit');
    
    el = entry.tag('rateQuantity');
    var rate_quantity_value = el.attr('value'),
        rate_quantity_unit = el.attr('unit');
    
    el = entry.tag('precondition').tag('value');
    var precondition_name = el.attr('displayName'),
        precondition_code = el.attr('code'),
        precondition_code_system = el.attr('codeSystem');
    
    el = entry.template('2.16.840.1.113883.10.20.1.28').tag('value');
    var reason_name = el.attr('displayName'),
        reason_code = el.attr('code'),
        reason_code_system = el.attr('codeSystem');
    
    el = entry.tag('routeCode');
    var route_name = el.attr('displayName'),
        route_code = el.attr('code'),
        route_code_system = el.attr('codeSystem'),
        route_code_system_name = el.attr('codeSystemName');
    
    // participant/playingEntity => vehicle
    el = entry.tag('participant').tag('playingEntity');
    var vehicle_name = el.tag('name').val();

    el = el.tag('code');
    // prefer the code vehicle_name but fall back to the non-coded one
    // (which for C32s is in fact the primary field for this info)
    vehicle_name = el.attr('displayName') || vehicle_name;
    var vehicle_code = el.attr('code'),
        vehicle_code_system = el.attr('codeSystem'),
        vehicle_code_system_name = el.attr('codeSystemName');
    
    el = entry.tag('administrationUnitCode');
    var administration_name = el.attr('displayName'),
        administration_code = el.attr('code'),
        administration_code_system = el.attr('codeSystem'),
        administration_code_system_name = el.attr('codeSystemName');
    
    // performer => prescriber
    el = entry.tag('performer');
    var prescriber_organization = el.tag('name').val(),
        prescriber_person = null;
    
    data.push({
      reference: reference,
      reference_title: referenceTitle,
      reference_sig: referenceSig,
      date_range: {
        start: start_date,
        end: end_date
      },
      text: text,
      product: {
        name: product_name,
        text: product_original_text,
        code: product_code,
        code_system: product_code_system,
        translation: {
          name: translation_name,
          code: translation_code,
          code_system: translation_code_system,
          code_system_name: translation_code_system_name
        }
      },
      dose_quantity: {
        value: dose_value,
        unit: dose_unit
      },
      rate_quantity: {
        value: rate_quantity_value,
        unit: rate_quantity_unit
      },
      precondition: {
        name: precondition_name,
        code: precondition_code,
        code_system: precondition_code_system
      },
      reason: {
        name: reason_name,
        code: reason_code,
        code_system: reason_code_system
      },
      route: {
        name: route_name,
        code: route_code,
        code_system: route_code_system,
        code_system_name: route_code_system_name
      },
      schedule: {
        type: schedule_type,
        period_value: schedule_period_value,
        period_unit: schedule_period_unit
      },
      vehicle: {
        name: vehicle_name,
        code: vehicle_code,
        code_system: vehicle_code_system,
        code_system_name: vehicle_code_system_name
      },
      administration: {
        name: administration_name,
        code: administration_code,
        code_system: administration_code_system,
        code_system_name: administration_code_system_name
      },
      prescriber: {
        organization: prescriber_organization,
        person: prescriber_person
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 problems section
 */

Parsers.C32.problems = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var problems = c32.section('problems');
  
  problems.entries().each(function(entry) {

    var reference = entry.tag('reference').attr('value');

    var referenceTitle = entry.tag('text').val();

    el = entry.tag('effectiveTime');
    var start_date = parseDate(el.tag('low').attr('value')),
        end_date = parseDate(el.tag('high').attr('value'));
    
    el = entry.template('2.16.840.1.113883.10.20.1.28').tag('value');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');

    // Pre-C32 CCDs put the problem name in this "originalText" field, and some vendors
    // continue doing this with their C32, even though it's not technically correct
    if (!name) {
      el = entry.template('2.16.840.1.113883.10.20.1.28').tag('originalText');
      if (!el.isEmpty()) {
        name = Core.stripWhitespace(el.val());
      }
    }

    el = entry.template('2.16.840.1.113883.10.20.1.28').tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
    el = entry.template('2.16.840.1.113883.10.20.1.50');
    var status = el.tag('value').attr('displayName');
    
    var age = null;
    el = entry.template('2.16.840.1.113883.10.20.1.38');
    if (!el.isEmpty()) {
      age = parseFloat(el.tag('value').attr('value'));
    }
    
    data.push({
      reference: reference,
      reference_title: referenceTitle,
      date_range: {
        start: start_date,
        end: end_date
      },
      name: name,
      status: status,
      age: age,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      translation: {
        name: translation_name,
        code: translation_code,
        code_system: translation_code_system,
        code_system_name: translation_code_system_name
      },
      comment: null // not part of C32
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 procedures section
 */

Parsers.C32.procedures = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var procedures = c32.section('procedures');
  
  procedures.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var date = parseDate(el.attr('value'));
    
    el = entry.tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem');

    if (!name) {
      name = Core.stripWhitespace(entry.tag('originalText').val());
    }
    
    // 'specimen' tag not always present
    el = entry.tag('specimen').tag('code');
    var specimen_name = el.attr('displayName'),
        specimen_code = el.attr('code'),
        specimen_code_system = el.attr('codeSystem');
    
    el = entry.tag('performer').tag('addr');
    var organization = el.tag('name').val(),
        phone = el.tag('telecom').attr('value');
    
    var performer_dict = parseAddress(el);
    performer_dict.organization = organization;
    performer_dict.phone = phone;
    
    // participant => device
    el = entry.tag('participant').tag('code');
    var device_name = el.attr('displayName'),
        device_code = el.attr('code'),
        device_code_system = el.attr('codeSystem');
    
    data.push({
      date: date,
      name: name,
      code: code,
      code_system: code_system,
      specimen: {
        name: specimen_name,
        code: specimen_code,
        code_system: specimen_code_system
      },
      performer: performer_dict,
      device: {
        name: device_name,
        code: device_code,
        code_system: device_code_system
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the C32 vitals section
 */

Parsers.C32.vitals = function (c32) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var vitals = c32.section('vitals');
  
  vitals.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var entry_date = parseDate(el.attr('value'));
    
    var result;
    var results = entry.elsByTag('component');
    var results_data = [];
    
    for (var j = 0; j < results.length; j++) {
      result = results[j];
      
      // Results
      
      el = result.tag('code');
      var name = el.attr('displayName'),
          code = el.attr('code'),
          code_system = el.attr('codeSystem'),
          code_system_name = el.attr('codeSystemName');
      
      el = result.tag('value');
      var value = parseFloat(el.attr('value')),
          unit = el.attr('unit');
      
      results_data.push({
        name: name,
        code: code,
        code_system: code_system,
        code_system_name: code_system_name,
        value: value,
        unit: unit
      });
    }
    
    data.push({
      date: entry_date,
      results: results_data
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA document
 */

Parsers.CCDA = (function () {
  
  var run = function (ccda) {
    var data = {};
    
    data.document              = Parsers.CCDA.document(ccda);
    data.allergies             = Parsers.CCDA.allergies(ccda);
    data.care_plan             = Parsers.CCDA.care_plan(ccda);
    data.chief_complaint       = Parsers.CCDA.free_text(ccda, 'chief_complaint');
    data.demographics          = Parsers.CCDA.demographics(ccda);
    data.encounters            = Parsers.CCDA.encounters(ccda);
    data.functional_statuses   = Parsers.CCDA.functional_statuses(ccda);
    data.immunizations         = Parsers.CCDA.immunizations(ccda).administered;
    data.immunization_declines = Parsers.CCDA.immunizations(ccda).declined;
    data.instructions          = Parsers.CCDA.instructions(ccda);
    data.results               = Parsers.CCDA.results(ccda);
    data.medications           = Parsers.CCDA.medications(ccda);
    data.problems              = Parsers.CCDA.problems(ccda);
    data.procedures            = Parsers.CCDA.procedures(ccda);
    data.smoking_status        = Parsers.CCDA.smoking_status(ccda);
    data.vitals                = Parsers.CCDA.vitals(ccda);
    
    data.json                        = Core.json;
    data.document.json               = Core.json;
    data.allergies.json              = Core.json;
    data.care_plan.json              = Core.json;
    data.chief_complaint.json        = Core.json;
    data.demographics.json           = Core.json;
    data.encounters.json             = Core.json;
    data.functional_statuses.json    = Core.json;
    data.immunizations.json          = Core.json;
    data.immunization_declines.json  = Core.json;
    data.instructions.json           = Core.json;
    data.results.json                = Core.json;
    data.medications.json            = Core.json;
    data.problems.json               = Core.json;
    data.procedures.json             = Core.json;
    data.smoking_status.json         = Core.json;
    data.vitals.json                 = Core.json;
    
    return data;
  };

  return {
    run: run
  };
  
})();
;

/*
 * Parser for the CCDA document section
 */

Parsers.CCDA.document = function (ccda) {

    var parseDate = Documents.parseDate;
    var parseName = Documents.parseName;
    var parseAddress = Documents.parseAddress;
    var parsePhones = Documents.parsePhones;
    var parseIds = Documents.parseIds;

    var data = {}, el;

    var doc = ccda.section('document');

    var date = parseDate(doc.tag('effectiveTime').attr('value'));
    var title = Core.stripWhitespace(doc.tag('title').val());

    var author = doc.tag('author');
    var assigned_author_oid = author.tag('assignedAuthor').tag('id');
    el = author.tag('assignedPerson').tag('name');
    var name_dict = parseName(el);

    var author_npi = author.tag('assignedAuthor').tag('id').attr('extension');

    el = author.tag('addr');
    var address_dict = parseAddress(el);

    el = author.tag('assignedAuthor').immediateChildrenTags('telecom');
    var author_phones = parsePhones(el);

    var documentation_of_list = [];
    var performers = doc.tag('documentationOf').elsByTag('performer');
    for (var i = 0; i < performers.length; i++) {
        el = performers[i];
        var performer_name_dict = parseName(el);
        var performer_phones = parsePhones(el.elsByTag('telecom'));

        console.log('CCDA');
        console.log(el.tag('telecom').attr('value'));
        console.log(el.elsByTag('telecom'));

        var performer_addr = parseAddress(el.tag('addr'));

        documentation_of_list.push({
            name: performer_name_dict,
            phones: performer_phones,
            address: performer_addr
        });
    }

    el = doc.tag('legalAuthenticator');

    var legal_date = parseDate(el.tag('time').attr('value'));
    var legal_assigned_person = parseName(el.tag('assignedEntity').tag('assignedPerson').tag('name'));
    var legal_org_address = parseAddress(el.tag('assignedEntity').tag('addr'));

    var el2 = el.tag('assignedEntity').immediateChildrenTags('id');
    var legal_ids = parseIds(el2);

    var idEl = el.tag('representedOrganization').immediateChildrenTags('id');
    var legal_org_ids = parseIds(idEl);

    var legal_org_name = el.tag('representedOrganization').tag('name').val();

    var phonesEl = el.tag('assignedEntity').immediateChildrenTags('telecom');
    var legal_org_phones = parsePhones(phonesEl);


    el = doc.tag('encompassingEncounter').tag('location');
    var location_name = Core.stripWhitespace(el.tag('name').val());
    var location_addr_dict = parseAddress(el.tag('addr'));

    var encounter_date = null;
    el = el.tag('effectiveTime');
    if (!el.isEmpty()) {
        encounter_date = parseDate(el.attr('value'));
    }

    var custodianName = doc.tag('custodian').tag('assignedCustodian').tag('representedCustodianOrganization').tag('name').val();


    data = {
        custodian: {
            name: custodianName
        },
        date: date,
        title: title,
        author: {
            npi: author_npi,
            name: name_dict,
            address: address_dict,
            phones: author_phones
        },
        documentation_of: documentation_of_list,
        legal_authenticator: {
            date: legal_date,
            ids: legal_ids,
            assigned_person: legal_assigned_person,
            representedOrganization: {
                ids: legal_org_ids,
                name: legal_org_name,
                phones: legal_org_phones,
                address: legal_org_address
            }
        },
        location: {
            name: location_name,
            address: location_addr_dict,
            encounter_date: encounter_date
        }
    };

    return data;
};
;

/*
 * Parser for the CCDA allergies section
 */

Parsers.CCDA.allergies = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var allergies = ccda.section('allergies');
  
  allergies.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var start_date = parseDate(el.tag('low').attr('value')),
        end_date = parseDate(el.tag('high').attr('value'));
    
    el = entry.template('2.16.840.1.113883.10.20.22.4.7').tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');
    
    // value => reaction_type
    el = entry.template('2.16.840.1.113883.10.20.22.4.7').tag('value');
    var reaction_type_name = el.attr('displayName'),
        reaction_type_code = el.attr('code'),
        reaction_type_code_system = el.attr('codeSystem'),
        reaction_type_code_system_name = el.attr('codeSystemName');
    
    // reaction
    el = entry.template('2.16.840.1.113883.10.20.22.4.9').tag('value');
    var reaction_name = el.attr('displayName'),
        reaction_code = el.attr('code'),
        reaction_code_system = el.attr('codeSystem');
    
    // severity
    el = entry.template('2.16.840.1.113883.10.20.22.4.8').tag('value');
    var severity = el.attr('displayName');
    
    // participant => allergen
    el = entry.tag('participant').tag('code');
    var allergen_name = el.attr('displayName'),
        allergen_code = el.attr('code'),
        allergen_code_system = el.attr('codeSystem'),
        allergen_code_system_name = el.attr('codeSystemName');

    // this is not a valid place to store the allergen name but some vendors use it
    if (!allergen_name) {
      el = entry.tag('participant').tag('name');
      if (!el.isEmpty()) {
        allergen_name = el.val();
      }
    }
    if (!allergen_name) {
      el = entry.template('2.16.840.1.113883.10.20.22.4.7').tag('originalText');
      if (!el.isEmpty()) {
        allergen_name = Core.stripWhitespace(el.val());
      }
    }
    
    // status
    el = entry.template('2.16.840.1.113883.10.20.22.4.28').tag('value');
    var status = el.attr('displayName');
    
    data.push({
      date_range: {
        start: start_date,
        end: end_date
      },
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      status: status,
      severity: severity,
      reaction: {
        name: reaction_name,
        code: reaction_code,
        code_system: reaction_code_system
      },
      reaction_type: {
        name: reaction_type_name,
        code: reaction_type_code,
        code_system: reaction_type_code_system,
        code_system_name: reaction_type_code_system_name
      },
      allergen: {
        name: allergen_name,
        code: allergen_code,
        code_system: allergen_code_system,
        code_system_name: allergen_code_system_name
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA "plan of care" section
 */

Parsers.CCDA.care_plan = function (ccda) {
  
  var data = [], el;
  
  var care_plan = ccda.section('care_plan');
  
  care_plan.entries().each(function(entry) {
    
    var name = null,
        code = null,
        code_system = null,
        code_system_name = null;

    // Plan of care encounters, which have no other details
    el = entry.template('2.16.840.1.113883.10.20.22.4.40');
    if (!el.isEmpty()) {
      name = 'encounter';
    } else {
      el = entry.tag('code');
      
      name = el.attr('displayName');
      code = el.attr('code');
      code_system = el.attr('codeSystem');
      code_system_name = el.attr('codeSystemName');
    }

    var text = Core.stripWhitespace(entry.tag('text').val());
    
    data.push({
      text: text,
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA demographics section
 */

Parsers.CCDA.demographics = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var parsePhones = Documents.parsePhones;
  var parseIds = Documents.parseIds;
  var data = {}, el;
  
  var demographics = ccda.section('demographics');
  
  var patient = demographics.tag('patientRole');
  el = patient.tag('patient').tag('name');
  var patient_name_dict = parseName(el);

  var mrn_number = patient.tag('id').attr('extension');

  el = patient.tag('patient');
  var dob = parseDate(el.tag('birthTime').attr('value')),
      gender = Core.Codes.gender(el.tag('administrativeGenderCode').attr('code')),
      marital_status = Core.Codes.maritalStatus(el.tag('maritalStatusCode').attr('code'));
  
  el = patient.tag('addr');
  var patient_address_dict = parseAddress(el);

  el = patient.immediateChildrenTags('telecom');

  var phones = parsePhones(el);

  /**
   * This works for an aprima CCDA
   *
   * @todo: Put this in the helpers file
   * @param telecoms
   * @returns {string}
   */
  var parseEmail = function (telecoms) {
    if (! telecoms.length > 0) {
      return null;
    }

    for (var i = 0; i < telecoms.length; i++) {
      var telecomValue = telecoms[i].attr('value');

      if (telecomValue == null) {
        continue;
      }

      if (! (telecomValue.indexOf('@') > -1)) {
        continue;
      }

      if (telecomValue.indexOf(':') > -1) {
        var exploded = telecomValue.split(':');
        return exploded[1].toString();
      }

      return telecomValue.toString();
    }
  };

  var parsedEmail = parseEmail(el);
  var email = parsedEmail ? parsedEmail : null;

  var language = patient.tag('languageCommunication').tag('languageCode').attr('code'),
      race = patient.tag('raceCode').attr('displayName'),
      ethnicity = patient.tag('ethnicGroupCode').attr('displayName'),
      religion = patient.tag('religiousAffiliationCode').attr('displayName');
  
  el = patient.tag('birthplace');
  var birthplace_dict = parseAddress(el);
  
  el = patient.tag('guardian');
  var guardian_relationship = el.tag('code').attr('displayName'),
      guardian_relationship_code = el.tag('code').attr('code'),
      guardian_home = el.tag('telecom').attr('value');
  
  el = el.tag('guardianPerson').tag('name');
  var guardian_name_dict = parseName(el);
  
  el = patient.tag('guardian').tag('addr');
  var guardian_address_dict = parseAddress(el);
  
  el = patient.tag('providerOrganization');
  var provider_organization = el.tag('name').val(),
      provider_phones = parsePhones(el.immediateChildrenTags('telecom')),
      provider_ids = parseIds(el.immediateChildrenTags('id')),
      provider_address_dict = parseAddress(el.tag('addr'));

  el = demographics.immediateChildrenTags('participant');

  var patient_contacts = el.map(function(tag) {
    tag = tag.tag('associatedEntity');

    return {
      relationship: Core.Codes.role(tag.attr('classCode')),
        relationship_code: {
        code: tag.tag('code').attr('code'),
        displayName: tag.tag('code').attr('displayName'),
      },
      phones: parsePhones(tag.immediateChildrenTags('telecom')),
      name: parseName(tag.tag('associatedPerson').tag('name'))
    }
  });
  
  data = {
    name: patient_name_dict,
    dob: dob,
    gender: gender,
    mrn_number: mrn_number,
    marital_status: marital_status,
    address: patient_address_dict,
    phones: phones,
    email: email,
    language: language,
    race: race,
    ethnicity: ethnicity,
    religion: religion,
    birthplace: {
      state: birthplace_dict.state,
      zip: birthplace_dict.zip,
      country: birthplace_dict.country
    },
    guardian: {
      name: {
        given: guardian_name_dict.given,
        family: guardian_name_dict.family
      },
      relationship: guardian_relationship,
      relationship_code: guardian_relationship_code,
      address: guardian_address_dict,
      phone: {
        home: guardian_home
      }
    },
    patient_contacts: patient_contacts,
    provider: {
        ids: provider_ids,
        organization: provider_organization,
      phones: provider_phones,
      address: provider_address_dict
    }
  };
  
  return data;
};
;

/*
 * Parser for the CCDA encounters section
 */

Parsers.CCDA.encounters = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var parseIds = Documents.parseIds;
  var data = [], el;
  
  var encounters = ccda.section('encounters');
  
  encounters.entries().each(function(entry) {
    
    var date = parseDate(entry.tag('effectiveTime').attr('value'));
    
    el = entry.tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName'),
        code_system_version = el.attr('codeSystemVersion');
    
    // translation
    el = entry.tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
    // performer
    el = entry.tag('performer').tag('code');
    var performer_name = el.attr('displayName'),
        performer_code = el.attr('code'),
        performer_code_system = el.attr('codeSystem'),
        performer_code_system_name = el.attr('codeSystemName'),
        performer_ids = parseIds(entry.tag('performer').tag('assignedEntity').immediateChildrenTags('id'));

    // participant => location
    el = entry.tag('participant');
    var organization = el.tag('code').attr('displayName');
    
    var location_dict = parseAddress(el);
    location_dict.organization = organization;

    // findings
    var findings = [];
    var findingEls = entry.elsByTag('entryRelationship');
    for (var i = 0; i < findingEls.length; i++) {
      el = findingEls[i].tag('value');
      findings.push({
        name: el.attr('displayName'),
        code: el.attr('code'),
        code_system: el.attr('codeSystem')
      });
    }
    
    data.push({
      date: date,
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      code_system_version: code_system_version,
      findings: findings,
      translation: {
        name: translation_name,
        code: translation_code,
        code_system: translation_code_system,
        code_system_name: translation_code_system_name
      },
      performer: {
        ids: performer_ids,
        name: performer_name,
        code: performer_code,
        code_system: performer_code_system,
        code_system_name: performer_code_system_name
      },
      location: location_dict
    });
  });
  
  return data;
};
;

/*
 * Parser for any freetext section (i.e., contains just a single <text> element)
 */

Parsers.CCDA.free_text = function (ccda, sectionName) {

  var data = {};
  
  var doc = ccda.section(sectionName);
  var text = Core.stripWhitespace(doc.tag('text').val());
  
  data = {
    text: text
  };

  return data;
};
;

/*
 * Parser for the CCDA functional & cognitive status
 */

Parsers.CCDA.functional_statuses = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var data = [], el;

  var statuses = ccda.section('functional_statuses');

  statuses.entries().each(function(entry) {

    var date = parseDate(entry.tag('effectiveTime').attr('value'));
    if (!date) {
      date = parseDate(entry.tag('effectiveTime').tag('low').attr('value'));
    }

    el = entry.tag('value');

    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');

    data.push({
      date: date,
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name
    });
  
  });
  
  return data;
};
;

/*
 * Parser for the CCDA immunizations section
 */

Parsers.CCDA.immunizations = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var administeredData = [], declinedData = [], el, product;
  
  var immunizations = ccda.section('immunizations');
  
  immunizations.entries().each(function(entry) {
    
    // date
    el = entry.tag('effectiveTime');
    var date = parseDate(el.attr('value'));
    if (!date) {
      date = parseDate(el.tag('low').attr('value'));
    }

    // if 'declined' is true, this is a record that this vaccine WASN'T administered
    el = entry.tag('substanceAdministration');
    var declined = el.boolAttr('negationInd');

    // product
    product = entry.template('2.16.840.1.113883.10.20.22.4.54');
    el = product.tag('code');
    var product_name = el.attr('displayName'),
        product_code = el.attr('code'),
        product_code_system = el.attr('codeSystem'),
        product_code_system_name = el.attr('codeSystemName');

    // translation
    el = product.tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');

    // misc product details
    el = product.tag('lotNumberText');
    var lot_number = el.val();

    el = product.tag('manufacturerOrganization');
    var manufacturer_name = el.tag('name').val();
    
    // route
    el = entry.tag('routeCode');
    var route_name = el.attr('displayName'),
        route_code = el.attr('code'),
        route_code_system = el.attr('codeSystem'),
        route_code_system_name = el.attr('codeSystemName');
    
    // instructions
    el = entry.template('2.16.840.1.113883.10.20.22.4.20');
    var instructions_text = Core.stripWhitespace(el.tag('text').val());
    el = el.tag('code');
    var education_name = el.attr('displayName'),
        education_code = el.attr('code'),
        education_code_system = el.attr('codeSystem');

    // dose
    el = entry.tag('doseQuantity');
    var dose_value = el.attr('value'),
        dose_unit = el.attr('unit');
    
    var data = (declined) ? declinedData : administeredData;
    data.push({
      date: date,
      product: {
        name: product_name,
        code: product_code,
        code_system: product_code_system,
        code_system_name: product_code_system_name,
        translation: {
          name: translation_name,
          code: translation_code,
          code_system: translation_code_system,
          code_system_name: translation_code_system_name
        },
        lot_number: lot_number,
        manufacturer_name: manufacturer_name
      },
      dose_quantity: {
        value: dose_value,
        unit: dose_unit
      },
      route: {
        name: route_name,
        code: route_code,
        code_system: route_code_system,
        code_system_name: route_code_system_name
      },
      instructions: instructions_text,
      education_type: {
        name: education_name,
        code: education_code,
        code_system: education_code_system
      }
    });
  });
  
  return {
    administered: administeredData,
    declined: declinedData
  };
};
;

/*
 * Parser for the CCDA "plan of care" section
 */

Parsers.CCDA.instructions = function (ccda) {
  
  var data = [], el;
  
  var instructions = ccda.section('instructions');
  
  instructions.entries().each(function(entry) {

    el = entry.tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');

    var text = Core.stripWhitespace(entry.tag('text').val());
    
    data.push({
      text: text,
      name: name,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA results (labs) section
 */

Parsers.CCDA.results = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var results = ccda.section('results');
  
  results.entries().each(function(entry) {
    
    // panel
    el = entry.tag('code');
    var panel_name = el.attr('displayName'),
        panel_code = el.attr('code'),
        panel_code_system = el.attr('codeSystem'),
        panel_code_system_name = el.attr('codeSystemName');
    
    var observation;
    var tests = entry.elsByTag('observation');
    var tests_data = [];
    
    for (var i = 0; i < tests.length; i++) {
      observation = tests[i];
      
      var date = parseDate(observation.tag('effectiveTime').attr('value'));
      
      el = observation.tag('code');
      var name = el.attr('displayName'),
          code = el.attr('code'),
          code_system = el.attr('codeSystem'),
          code_system_name = el.attr('codeSystemName');

      if (!name) {
        name = Core.stripWhitespace(observation.tag('text').val());
      }
      
      el = observation.tag('translation');
      var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
      el = observation.tag('value');
      var value = el.attr('value'),
          unit = el.attr('unit');
      // We could look for xsi:type="PQ" (physical quantity) but it seems better
      // not to trust that that field has been used correctly...
      if (value && !isNaN(parseFloat(value))) {
        value = parseFloat(value);
      }
      if (!value) {
        value = el.val(); // look for free-text values
      }
      
      el = observation.tag('referenceRange');
      var reference_range_text = Core.stripWhitespace(el.tag('observationRange').tag('text').val()),
          reference_range_low_unit = el.tag('observationRange').tag('low').attr('unit'),
          reference_range_low_value = el.tag('observationRange').tag('low').attr('value'),
          reference_range_high_unit = el.tag('observationRange').tag('high').attr('unit'),
          reference_range_high_value = el.tag('observationRange').tag('high').attr('value');
      
      tests_data.push({
        date: date,
        name: name,
        value: value,
        unit: unit,
        code: code,
        code_system: code_system,
        code_system_name: code_system_name,
        translation: {
          name: translation_name,
          code: translation_code,
          code_system: translation_code_system,
          code_system_name: translation_code_system_name
        },
        reference_range: {
          text: reference_range_text,
          low_unit: reference_range_low_unit,
          low_value: reference_range_low_value,
          high_unit: reference_range_high_unit,
          high_value: reference_range_high_value,
        }
      });
    }
    
    data.push({
      name: panel_name,
      code: panel_code,
      code_system: panel_code_system,
      code_system_name: panel_code_system_name,
      tests: tests_data
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA medications section
 */

Parsers.CCDA.medications = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var data = [], el;
  
  var medications = ccda.section('medications');


  medications.entries().each(function(entry) {
    var status = entry.tag('value').attr('displayName');

    var reference = entry.tag('reference').attr('value');
    var referenceTitle = entry.tag('text').val();

    if (reference != null) {
      var referenceSig = ccda.content('sig-' + reference.split('-')[1]).val();
    }

    el = entry.tag('text');
    var sig = Core.stripWhitespace(el.val());

    var effectiveTimes = entry.elsByTag('effectiveTime');

    el = effectiveTimes[0]; // the first effectiveTime is the med start date
    var start_date = null, end_date = null;
    if (el) {
      start_date = parseDate(el.tag('low').attr('value'));
      end_date = parseDate(el.tag('high').attr('value'));
    }

    // the second effectiveTime might the schedule period or it might just
    // be a random effectiveTime from further in the entry... xsi:type should tell us
    el = effectiveTimes[1];
    var schedule_type = null, schedule_period_value = null, schedule_period_unit = null;
    if (el && el.attr('xsi:type') === 'PIVL_TS') {
      var institutionSpecified = el.attr('institutionSpecified');
      if (institutionSpecified === 'true') {
        schedule_type= 'frequency';
      } else if (institutionSpecified === 'false') {
        schedule_type = 'interval';
      }

      el = el.tag('period');
      schedule_period_value = el.attr('value');
      schedule_period_unit = el.attr('unit');
    }
    
    el = entry.tag('manufacturedProduct').tag('code');
    var product_name = el.attr('displayName'),
        product_code = el.attr('code'),
        product_code_system = el.attr('codeSystem');

    var product_original_text = null;
    el = entry.tag('manufacturedProduct').tag('originalText');
    if (!el.isEmpty()) {
      product_original_text = Core.stripWhitespace(el.val());
    }
    // if we don't have a product name yet, try the originalText version
    if (!product_name && product_original_text) {
      product_name = product_original_text;
    }
    
    el = entry.tag('manufacturedProduct').tag('translation');
    var translation_name = el.attr('displayName'),
        translation_code = el.attr('code'),
        translation_code_system = el.attr('codeSystem'),
        translation_code_system_name = el.attr('codeSystemName');
    
    el = entry.tag('doseQuantity');
    var dose_value = el.attr('value'),
        dose_unit = el.attr('unit');
    
    el = entry.tag('rateQuantity');
    var rate_quantity_value = el.attr('value'),
        rate_quantity_unit = el.attr('unit');
    
    el = entry.tag('precondition').tag('value');
    var precondition_name = el.attr('displayName'),
        precondition_code = el.attr('code'),
        precondition_code_system = el.attr('codeSystem');
    
    el = entry.template('2.16.840.1.113883.10.20.22.4.19').tag('value');
    var reason_name = el.attr('displayName'),
        reason_code = el.attr('code'),
        reason_code_system = el.attr('codeSystem');
    
    el = entry.tag('routeCode');
    var route_name = el.attr('displayName'),
        route_code = el.attr('code'),
        route_code_system = el.attr('codeSystem'),
        route_code_system_name = el.attr('codeSystemName');
    
    // participant/playingEntity => vehicle
    el = entry.tag('participant').tag('playingEntity');
    var vehicle_name = el.tag('name').val();

    el = el.tag('code');
    // prefer the code vehicle_name but fall back to the non-coded one
    vehicle_name = el.attr('displayName') || vehicle_name;
    var vehicle_code = el.attr('code'),
        vehicle_code_system = el.attr('codeSystem'),
        vehicle_code_system_name = el.attr('codeSystemName');
    
    el = entry.tag('administrationUnitCode');
    var administration_name = el.attr('displayName'),
        administration_code = el.attr('code'),
        administration_code_system = el.attr('codeSystem'),
        administration_code_system_name = el.attr('codeSystemName');
    
    // performer => prescriber
    el = entry.tag('performer');
    var prescriber_organization = el.tag('name').val(),
        prescriber_person = null;
    
    data.push({
      reference: reference,
      reference_title: referenceTitle,
      reference_sig: referenceSig,
      date_range: {
        start: start_date,
        end: end_date
      },
      status: status,
      text: sig,
      product: {
        name: product_name,
        code: product_code,
        code_system: product_code_system,
        text: product_original_text,
        translation: {
          name: translation_name,
          code: translation_code,
          code_system: translation_code_system,
          code_system_name: translation_code_system_name
        }
      },
      dose_quantity: {
        value: dose_value,
        unit: dose_unit
      },
      rate_quantity: {
        value: rate_quantity_value,
        unit: rate_quantity_unit
      },
      precondition: {
        name: precondition_name,
        code: precondition_code,
        code_system: precondition_code_system
      },
      reason: {
        name: reason_name,
        code: reason_code,
        code_system: reason_code_system
      },
      route: {
        name: route_name,
        code: route_code,
        code_system: route_code_system,
        code_system_name: route_code_system_name
      },
      schedule: {
        type: schedule_type,
        period_value: schedule_period_value,
        period_unit: schedule_period_unit
      },
      vehicle: {
        name: vehicle_name,
        code: vehicle_code,
        code_system: vehicle_code_system,
        code_system_name: vehicle_code_system_name
      },
      administration: {
        name: administration_name,
        code: administration_code,
        code_system: administration_code_system,
        code_system_name: administration_code_system_name
      },
      prescriber: {
        organization: prescriber_organization,
        person: prescriber_person
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA problems section
 */

Parsers.CCDA.problems = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var problems = ccda.section('problems');
  
  problems.entries().each(function(entry) {

    var reference = entry.tag('reference').attr('value');

    var referenceTitle = entry.tag('text').val();

    el = entry.tag('effectiveTime');
    var start_date = parseDate(el.tag('low').attr('value')),
        end_date = parseDate(el.tag('high').attr('value'));
    
    el = entry.template('2.16.840.1.113883.10.20.22.4.4').tag('value');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem'),
        code_system_name = el.attr('codeSystemName');
    
    el = entry.template('2.16.840.1.113883.10.20.22.4.4').tag('translation');
    var translation_name = el.attr('displayName'),
      translation_code = el.attr('code'),
      translation_code_system = el.attr('codeSystem'),
      translation_code_system_name = el.attr('codeSystemName');
    
    el = entry.template('2.16.840.1.113883.10.20.22.4.6');
    var status = el.tag('value').attr('displayName');
    
    var age = null;
    el = entry.template('2.16.840.1.113883.10.20.22.4.31');
    if (!el.isEmpty()) {
      age = parseFloat(el.tag('value').attr('value'));
    }

    el = entry.template('2.16.840.1.113883.10.20.22.4.64');
    var comment = Core.stripWhitespace(el.tag('text').val());
    
    data.push({
      reference: reference,
      reference_title: referenceTitle,
      date_range: {
        start: start_date,
        end: end_date
      },
      name: name,
      status: status,
      age: age,
      code: code,
      code_system: code_system,
      code_system_name: code_system_name,
      translation: {
        name: translation_name,
        code: translation_code,
        code_system: translation_code_system,
        code_system_name: translation_code_system_name
      },
      comment: comment
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA procedures section
 */

Parsers.CCDA.procedures = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var procedures = ccda.section('procedures');
  
  procedures.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var date = parseDate(el.attr('value'));
    
    el = entry.tag('code');
    var name = el.attr('displayName'),
        code = el.attr('code'),
        code_system = el.attr('codeSystem');

    if (!name) {
      name = Core.stripWhitespace(entry.tag('originalText').val());
    }
    
    // 'specimen' tag not always present
    el = entry.tag('specimen').tag('code');
    var specimen_name = el.attr('displayName'),
        specimen_code = el.attr('code'),
        specimen_code_system = el.attr('codeSystem');
    
    el = entry.tag('performer').tag('addr');
    var organization = el.tag('name').val(),
        phone = el.tag('telecom').attr('value');
    
    var performer_dict = parseAddress(el);
    performer_dict.organization = organization;
    performer_dict.phone = phone;
    
    // participant => device
    el = entry.template('2.16.840.1.113883.10.20.22.4.37').tag('code');
    var device_name = el.attr('displayName'),
        device_code = el.attr('code'),
        device_code_system = el.attr('codeSystem');
    
    data.push({
      date: date,
      name: name,
      code: code,
      code_system: code_system,
      specimen: {
        name: specimen_name,
        code: specimen_code,
        code_system: specimen_code_system
      },
      performer: performer_dict,
      device: {
        name: device_name,
        code: device_code,
        code_system: device_code_system
      }
    });
  });
  
  return data;
};
;

/*
 * Parser for the CCDA smoking status in social history section
 */

Parsers.CCDA.smoking_status = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data, el;

  var name = null,
      code = null,
      code_system = null,
      code_system_name = null,
      entry_date = null;

  // We can parse all of the social_history sections,
  // but in practice, this section seems to be used for
  // smoking status, so we're just going to break that out.
  // And we're just looking for the first non-empty one.
  var social_history = ccda.section('social_history');
  var entries = social_history.entries();
  for (var i=0; i < entries.length; i++) {
    var entry = entries[i];

    var smoking_status = entry.template('2.16.840.1.113883.10.20.22.4.78');
    if (smoking_status.isEmpty()) {
      smoking_status = entry.template('2.16.840.1.113883.10.22.4.78');
    }
    if (smoking_status.isEmpty()) {
      continue;
    }

    el = smoking_status.tag('effectiveTime');
    entry_date = parseDate(el.attr('value'));

    el = smoking_status.tag('value');
    name = el.attr('displayName');
    code = el.attr('code');
    code_system = el.attr('codeSystem');
    code_system_name = el.attr('codeSystemName');

    if (name) {
      break;
    }
  }

  data = {
    date: entry_date,
    name: name,
    code: code,
    code_system: code_system,
    code_system_name: code_system_name
  };
  
  return data;
};
;

/*
 * Parser for the CCDA vitals section
 */

Parsers.CCDA.vitals = function (ccda) {
  
  var parseDate = Documents.parseDate;
  var parseName = Documents.parseName;
  var parseAddress = Documents.parseAddress;
  var data = [], el;
  
  var vitals = ccda.section('vitals');
  
  vitals.entries().each(function(entry) {
    
    el = entry.tag('effectiveTime');
    var entry_date = parseDate(el.attr('value'));
    
    var result;
    var results = entry.elsByTag('component');
    var results_data = [];
    
    for (var i = 0; i < results.length; i++) {
      result = results[i];
      
      el = result.tag('code');
      var name = el.attr('displayName'),
          code = el.attr('code'),
          code_system = el.attr('codeSystem'),
          code_system_name = el.attr('codeSystemName');
      
      el = result.tag('value');
      var value = parseFloat(el.attr('value')),
          unit = el.attr('unit');
      
      results_data.push({
        name: name,
        code: code,
        code_system: code_system,
        code_system_name: code_system_name,
        value: value,
        unit: unit
      });
    }
    
    data.push({
      date: entry_date,
      results: results_data
    });
  });
  
  return data;
};
;

/*
 * ...
 */

/* exported Renderers */
var Renderers = (function () {
  
  var method = function () {};
  
  return {
    method: method
  };
  
})();
;

/*
 * ...
 */

/* exported BlueButton */
var BlueButton = function (source, opts) {
  var type, parsedData, parsedDocument;
  
  // Look for options
  if (!opts) opts = {};
  
  // Detect and parse the source data
  parsedData = Core.parseData(source);
  
  // Detect and parse the document
  if (opts.parser) {
    
    // TODO: parse the document with provided custom parser
    parsedDocument = opts.parser();
    
  } else {
    type = Documents.detect(parsedData);
    switch (type) {
      case 'c32':
        parsedData = Documents.C32.process(parsedData);
        parsedDocument = Parsers.C32.run(parsedData);
        break;
      case 'ccda':
        parsedData = Documents.CCDA.process(parsedData);
        parsedDocument = Parsers.CCDA.run(parsedData);
        break;
      case 'json':
        /* Expects a call like:
         * BlueButton(json string, {
         *   generatorType: 'ccda',
         *   template: < EJS file contents >
         * })
         * The returned "type" will be the requested type (not "json")
         * and the XML will be turned as a string in the 'data' key
         */
        switch (opts.generatorType) {
          // only the unit tests ever need to worry about this testingMode argument
          case 'c32':
            type = 'c32';
            parsedDocument = Generators.C32.run(parsedData, opts.template, opts.testingMode);
            break;
          case 'ccda':
            type = 'ccda';
            parsedDocument = Generators.CCDA.run(parsedData, opts.template, opts.testingMode);
            break;
        }
    }
  }
  
  return {
    type: type,
    data: parsedDocument,
    source: parsedData
  };

};


return BlueButton;

}));

/**
 * material-design-lite - Material Design Components in CSS, JS and HTML
 * @version v1.1.0
 * @license Apache-2.0
 * @copyright 2015 Google, Inc.
 * @link https://github.com/google/material-design-lite
 */
!function(){"use strict";function e(e,t){if(e){if(t.element_.classList.contains(t.CssClasses_.MDL_JS_RIPPLE_EFFECT)){var s=document.createElement("span");s.classList.add(t.CssClasses_.MDL_RIPPLE_CONTAINER),s.classList.add(t.CssClasses_.MDL_JS_RIPPLE_EFFECT);var i=document.createElement("span");i.classList.add(t.CssClasses_.MDL_RIPPLE),s.appendChild(i),e.appendChild(s)}e.addEventListener("click",function(s){s.preventDefault();var i=e.href.split("#")[1],n=t.element_.querySelector("#"+i);t.resetTabState_(),t.resetPanelState_(),e.classList.add(t.CssClasses_.ACTIVE_CLASS),n.classList.add(t.CssClasses_.ACTIVE_CLASS)})}}function t(e,t,s,i){function n(){var n=e.href.split("#")[1],a=i.content_.querySelector("#"+n);i.resetTabState_(t),i.resetPanelState_(s),e.classList.add(i.CssClasses_.IS_ACTIVE),a.classList.add(i.CssClasses_.IS_ACTIVE)}if(i.tabBar_.classList.contains(i.CssClasses_.JS_RIPPLE_EFFECT)){var a=document.createElement("span");a.classList.add(i.CssClasses_.RIPPLE_CONTAINER),a.classList.add(i.CssClasses_.JS_RIPPLE_EFFECT);var l=document.createElement("span");l.classList.add(i.CssClasses_.RIPPLE),a.appendChild(l),e.appendChild(a)}e.addEventListener("click",function(t){"#"===e.getAttribute("href").charAt(0)&&(t.preventDefault(),n())}),e.show=n,e.addEventListener("click",function(n){n.preventDefault();var a=e.href.split("#")[1],l=i.content_.querySelector("#"+a);i.resetTabState_(t),i.resetPanelState_(s),e.classList.add(i.CssClasses_.IS_ACTIVE),l.classList.add(i.CssClasses_.IS_ACTIVE)})}var s={upgradeDom:function(e,t){},upgradeElement:function(e,t){},upgradeElements:function(e){},upgradeAllRegistered:function(){},registerUpgradedCallback:function(e,t){},register:function(e){},downgradeElements:function(e){}};s=function(){function e(e,t){for(var s=0;s<h.length;s++)if(h[s].className===e)return"undefined"!=typeof t&&(h[s]=t),h[s];return!1}function t(e){var t=e.getAttribute("data-upgraded");return null===t?[""]:t.split(",")}function s(e,s){var i=t(e);return-1!==i.indexOf(s)}function i(t,s){if("undefined"==typeof t&&"undefined"==typeof s)for(var a=0;a<h.length;a++)i(h[a].className,h[a].cssClass);else{var l=t;if("undefined"==typeof s){var o=e(l);o&&(s=o.cssClass)}for(var r=document.querySelectorAll("."+s),_=0;_<r.length;_++)n(r[_],l)}}function n(i,n){if(!("object"==typeof i&&i instanceof Element))throw new Error("Invalid argument provided to upgrade MDL element.");var a=t(i),l=[];if(n)s(i,n)||l.push(e(n));else{var o=i.classList;h.forEach(function(e){o.contains(e.cssClass)&&-1===l.indexOf(e)&&!s(i,e.className)&&l.push(e)})}for(var r,_=0,d=l.length;d>_;_++){if(r=l[_],!r)throw new Error("Unable to find a registered component for the given class.");a.push(r.className),i.setAttribute("data-upgraded",a.join(","));var C=new r.classConstructor(i);C[p]=r,c.push(C);for(var u=0,E=r.callbacks.length;E>u;u++)r.callbacks[u](i);r.widget&&(i[r.className]=C);var m=document.createEvent("Events");m.initEvent("mdl-componentupgraded",!0,!0),i.dispatchEvent(m)}}function a(e){Array.isArray(e)||(e="function"==typeof e.item?Array.prototype.slice.call(e):[e]);for(var t,s=0,i=e.length;i>s;s++)t=e[s],t instanceof HTMLElement&&(n(t),t.children.length>0&&a(t.children))}function l(t){var s="undefined"==typeof t.widget&&"undefined"==typeof t.widget,i=!0;s||(i=t.widget||t.widget);var n={classConstructor:t.constructor||t.constructor,className:t.classAsString||t.classAsString,cssClass:t.cssClass||t.cssClass,widget:i,callbacks:[]};if(h.forEach(function(e){if(e.cssClass===n.cssClass)throw new Error("The provided cssClass has already been registered: "+e.cssClass);if(e.className===n.className)throw new Error("The provided className has already been registered")}),t.constructor.prototype.hasOwnProperty(p))throw new Error("MDL component classes must not have "+p+" defined as a property.");var a=e(t.classAsString,n);a||h.push(n)}function o(t,s){var i=e(t);i&&i.callbacks.push(s)}function r(){for(var e=0;e<h.length;e++)i(h[e].className)}function _(e){var t=c.indexOf(e);c.splice(t,1);var s=e.element_.getAttribute("data-upgraded").split(","),i=s.indexOf(e[p].classAsString);s.splice(i,1),e.element_.setAttribute("data-upgraded",s.join(","));var n=document.createEvent("Events");n.initEvent("mdl-componentdowngraded",!0,!0),e.element_.dispatchEvent(n)}function d(e){var t=function(e){c.filter(function(t){return t.element_===e}).forEach(_)};if(e instanceof Array||e instanceof NodeList)for(var s=0;s<e.length;s++)t(e[s]);else{if(!(e instanceof Node))throw new Error("Invalid argument provided to downgrade MDL nodes.");t(e)}}var h=[],c=[],p="mdlComponentConfigInternal_";return{upgradeDom:i,upgradeElement:n,upgradeElements:a,upgradeAllRegistered:r,registerUpgradedCallback:o,register:l,downgradeElements:d}}(),s.ComponentConfigPublic,s.ComponentConfig,s.Component,s.upgradeDom=s.upgradeDom,s.upgradeElement=s.upgradeElement,s.upgradeElements=s.upgradeElements,s.upgradeAllRegistered=s.upgradeAllRegistered,s.registerUpgradedCallback=s.registerUpgradedCallback,s.register=s.register,s.downgradeElements=s.downgradeElements,window.componentHandler=s,window.componentHandler=s,window.addEventListener("load",function(){"classList"in document.createElement("div")&&"querySelector"in document&&"addEventListener"in window&&Array.prototype.forEach?(document.documentElement.classList.add("mdl-js"),s.upgradeAllRegistered()):(s.upgradeElement=function(){},s.register=function(){})}),Date.now||(Date.now=function(){return(new Date).getTime()},Date.now=Date.now);for(var i=["webkit","moz"],n=0;n<i.length&&!window.requestAnimationFrame;++n){var a=i[n];window.requestAnimationFrame=window[a+"RequestAnimationFrame"],window.cancelAnimationFrame=window[a+"CancelAnimationFrame"]||window[a+"CancelRequestAnimationFrame"],window.requestAnimationFrame=window.requestAnimationFrame,window.cancelAnimationFrame=window.cancelAnimationFrame}if(/iP(ad|hone|od).*OS 6/.test(window.navigator.userAgent)||!window.requestAnimationFrame||!window.cancelAnimationFrame){var l=0;window.requestAnimationFrame=function(e){var t=Date.now(),s=Math.max(l+16,t);return setTimeout(function(){e(l=s)},s-t)},window.cancelAnimationFrame=clearTimeout,window.requestAnimationFrame=window.requestAnimationFrame,window.cancelAnimationFrame=window.cancelAnimationFrame}var o=function(e){this.element_=e,this.init()};window.MaterialButton=o,o.prototype.Constant_={},o.prototype.CssClasses_={RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_CONTAINER:"mdl-button__ripple-container",RIPPLE:"mdl-ripple"},o.prototype.blurHandler_=function(e){e&&this.element_.blur()},o.prototype.disable=function(){this.element_.disabled=!0},o.prototype.disable=o.prototype.disable,o.prototype.enable=function(){this.element_.disabled=!1},o.prototype.enable=o.prototype.enable,o.prototype.init=function(){if(this.element_){if(this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT)){var e=document.createElement("span");e.classList.add(this.CssClasses_.RIPPLE_CONTAINER),this.rippleElement_=document.createElement("span"),this.rippleElement_.classList.add(this.CssClasses_.RIPPLE),e.appendChild(this.rippleElement_),this.boundRippleBlurHandler=this.blurHandler_.bind(this),this.rippleElement_.addEventListener("mouseup",this.boundRippleBlurHandler),this.element_.appendChild(e)}this.boundButtonBlurHandler=this.blurHandler_.bind(this),this.element_.addEventListener("mouseup",this.boundButtonBlurHandler),this.element_.addEventListener("mouseleave",this.boundButtonBlurHandler)}},s.register({constructor:o,classAsString:"MaterialButton",cssClass:"mdl-js-button",widget:!0});var r=function(e){this.element_=e,this.init()};window.MaterialCheckbox=r,r.prototype.Constant_={TINY_TIMEOUT:.001},r.prototype.CssClasses_={INPUT:"mdl-checkbox__input",BOX_OUTLINE:"mdl-checkbox__box-outline",FOCUS_HELPER:"mdl-checkbox__focus-helper",TICK_OUTLINE:"mdl-checkbox__tick-outline",RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE_CONTAINER:"mdl-checkbox__ripple-container",RIPPLE_CENTER:"mdl-ripple--center",RIPPLE:"mdl-ripple",IS_FOCUSED:"is-focused",IS_DISABLED:"is-disabled",IS_CHECKED:"is-checked",IS_UPGRADED:"is-upgraded"},r.prototype.onChange_=function(e){this.updateClasses_()},r.prototype.onFocus_=function(e){this.element_.classList.add(this.CssClasses_.IS_FOCUSED)},r.prototype.onBlur_=function(e){this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},r.prototype.onMouseUp_=function(e){this.blur_()},r.prototype.updateClasses_=function(){this.checkDisabled(),this.checkToggleState()},r.prototype.blur_=function(){window.setTimeout(function(){this.inputElement_.blur()}.bind(this),this.Constant_.TINY_TIMEOUT)},r.prototype.checkToggleState=function(){this.inputElement_.checked?this.element_.classList.add(this.CssClasses_.IS_CHECKED):this.element_.classList.remove(this.CssClasses_.IS_CHECKED)},r.prototype.checkToggleState=r.prototype.checkToggleState,r.prototype.checkDisabled=function(){this.inputElement_.disabled?this.element_.classList.add(this.CssClasses_.IS_DISABLED):this.element_.classList.remove(this.CssClasses_.IS_DISABLED)},r.prototype.checkDisabled=r.prototype.checkDisabled,r.prototype.disable=function(){this.inputElement_.disabled=!0,this.updateClasses_()},r.prototype.disable=r.prototype.disable,r.prototype.enable=function(){this.inputElement_.disabled=!1,this.updateClasses_()},r.prototype.enable=r.prototype.enable,r.prototype.check=function(){this.inputElement_.checked=!0,this.updateClasses_()},r.prototype.check=r.prototype.check,r.prototype.uncheck=function(){this.inputElement_.checked=!1,this.updateClasses_()},r.prototype.uncheck=r.prototype.uncheck,r.prototype.init=function(){if(this.element_){this.inputElement_=this.element_.querySelector("."+this.CssClasses_.INPUT);var e=document.createElement("span");e.classList.add(this.CssClasses_.BOX_OUTLINE);var t=document.createElement("span");t.classList.add(this.CssClasses_.FOCUS_HELPER);var s=document.createElement("span");if(s.classList.add(this.CssClasses_.TICK_OUTLINE),e.appendChild(s),this.element_.appendChild(t),this.element_.appendChild(e),this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT)){this.element_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS),this.rippleContainerElement_=document.createElement("span"),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CONTAINER),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_EFFECT),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CENTER),this.boundRippleMouseUp=this.onMouseUp_.bind(this),this.rippleContainerElement_.addEventListener("mouseup",this.boundRippleMouseUp);var i=document.createElement("span");i.classList.add(this.CssClasses_.RIPPLE),this.rippleContainerElement_.appendChild(i),this.element_.appendChild(this.rippleContainerElement_)}this.boundInputOnChange=this.onChange_.bind(this),this.boundInputOnFocus=this.onFocus_.bind(this),this.boundInputOnBlur=this.onBlur_.bind(this),this.boundElementMouseUp=this.onMouseUp_.bind(this),this.inputElement_.addEventListener("change",this.boundInputOnChange),this.inputElement_.addEventListener("focus",this.boundInputOnFocus),this.inputElement_.addEventListener("blur",this.boundInputOnBlur),this.element_.addEventListener("mouseup",this.boundElementMouseUp),this.updateClasses_(),this.element_.classList.add(this.CssClasses_.IS_UPGRADED)}},s.register({constructor:r,classAsString:"MaterialCheckbox",cssClass:"mdl-js-checkbox",widget:!0});var _=function(e){this.element_=e,this.init()};window.MaterialIconToggle=_,_.prototype.Constant_={TINY_TIMEOUT:.001},_.prototype.CssClasses_={INPUT:"mdl-icon-toggle__input",JS_RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE_CONTAINER:"mdl-icon-toggle__ripple-container",RIPPLE_CENTER:"mdl-ripple--center",RIPPLE:"mdl-ripple",IS_FOCUSED:"is-focused",IS_DISABLED:"is-disabled",IS_CHECKED:"is-checked"},_.prototype.onChange_=function(e){this.updateClasses_()},_.prototype.onFocus_=function(e){this.element_.classList.add(this.CssClasses_.IS_FOCUSED)},_.prototype.onBlur_=function(e){this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},_.prototype.onMouseUp_=function(e){this.blur_()},_.prototype.updateClasses_=function(){this.checkDisabled(),this.checkToggleState()},_.prototype.blur_=function(){window.setTimeout(function(){this.inputElement_.blur()}.bind(this),this.Constant_.TINY_TIMEOUT)},_.prototype.checkToggleState=function(){this.inputElement_.checked?this.element_.classList.add(this.CssClasses_.IS_CHECKED):this.element_.classList.remove(this.CssClasses_.IS_CHECKED)},_.prototype.checkToggleState=_.prototype.checkToggleState,_.prototype.checkDisabled=function(){this.inputElement_.disabled?this.element_.classList.add(this.CssClasses_.IS_DISABLED):this.element_.classList.remove(this.CssClasses_.IS_DISABLED)},_.prototype.checkDisabled=_.prototype.checkDisabled,_.prototype.disable=function(){this.inputElement_.disabled=!0,this.updateClasses_()},_.prototype.disable=_.prototype.disable,_.prototype.enable=function(){this.inputElement_.disabled=!1,this.updateClasses_()},_.prototype.enable=_.prototype.enable,_.prototype.check=function(){this.inputElement_.checked=!0,this.updateClasses_()},_.prototype.check=_.prototype.check,_.prototype.uncheck=function(){this.inputElement_.checked=!1,this.updateClasses_()},_.prototype.uncheck=_.prototype.uncheck,_.prototype.init=function(){if(this.element_){if(this.inputElement_=this.element_.querySelector("."+this.CssClasses_.INPUT),this.element_.classList.contains(this.CssClasses_.JS_RIPPLE_EFFECT)){this.element_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS),this.rippleContainerElement_=document.createElement("span"),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CONTAINER),this.rippleContainerElement_.classList.add(this.CssClasses_.JS_RIPPLE_EFFECT),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CENTER),this.boundRippleMouseUp=this.onMouseUp_.bind(this),this.rippleContainerElement_.addEventListener("mouseup",this.boundRippleMouseUp);var e=document.createElement("span");e.classList.add(this.CssClasses_.RIPPLE),this.rippleContainerElement_.appendChild(e),this.element_.appendChild(this.rippleContainerElement_)}this.boundInputOnChange=this.onChange_.bind(this),this.boundInputOnFocus=this.onFocus_.bind(this),this.boundInputOnBlur=this.onBlur_.bind(this),this.boundElementOnMouseUp=this.onMouseUp_.bind(this),this.inputElement_.addEventListener("change",this.boundInputOnChange),this.inputElement_.addEventListener("focus",this.boundInputOnFocus),this.inputElement_.addEventListener("blur",this.boundInputOnBlur),this.element_.addEventListener("mouseup",this.boundElementOnMouseUp),this.updateClasses_(),this.element_.classList.add("is-upgraded")}},s.register({constructor:_,classAsString:"MaterialIconToggle",cssClass:"mdl-js-icon-toggle",widget:!0});var d=function(e){this.element_=e,this.init()};window.MaterialMenu=d,d.prototype.Constant_={TRANSITION_DURATION_SECONDS:.3,TRANSITION_DURATION_FRACTION:.8,CLOSE_TIMEOUT:150},d.prototype.Keycodes_={ENTER:13,ESCAPE:27,SPACE:32,UP_ARROW:38,DOWN_ARROW:40},d.prototype.CssClasses_={CONTAINER:"mdl-menu__container",OUTLINE:"mdl-menu__outline",ITEM:"mdl-menu__item",ITEM_RIPPLE_CONTAINER:"mdl-menu__item-ripple-container",RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE:"mdl-ripple",IS_UPGRADED:"is-upgraded",IS_VISIBLE:"is-visible",IS_ANIMATING:"is-animating",BOTTOM_LEFT:"mdl-menu--bottom-left",BOTTOM_RIGHT:"mdl-menu--bottom-right",TOP_LEFT:"mdl-menu--top-left",TOP_RIGHT:"mdl-menu--top-right",UNALIGNED:"mdl-menu--unaligned"},d.prototype.init=function(){if(this.element_){var e=document.createElement("div");e.classList.add(this.CssClasses_.CONTAINER),this.element_.parentElement.insertBefore(e,this.element_),this.element_.parentElement.removeChild(this.element_),e.appendChild(this.element_),this.container_=e;var t=document.createElement("div");t.classList.add(this.CssClasses_.OUTLINE),this.outline_=t,e.insertBefore(t,this.element_);var s=this.element_.getAttribute("for")||this.element_.getAttribute("data-mdl-for"),i=null;s&&(i=document.getElementById(s),i&&(this.forElement_=i,i.addEventListener("click",this.handleForClick_.bind(this)),i.addEventListener("keydown",this.handleForKeyboardEvent_.bind(this))));var n=this.element_.querySelectorAll("."+this.CssClasses_.ITEM);this.boundItemKeydown_=this.handleItemKeyboardEvent_.bind(this),this.boundItemClick_=this.handleItemClick_.bind(this);for(var a=0;a<n.length;a++)n[a].addEventListener("click",this.boundItemClick_),n[a].tabIndex="-1",n[a].addEventListener("keydown",this.boundItemKeydown_);if(this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT))for(this.element_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS),a=0;a<n.length;a++){var l=n[a],o=document.createElement("span");o.classList.add(this.CssClasses_.ITEM_RIPPLE_CONTAINER);var r=document.createElement("span");r.classList.add(this.CssClasses_.RIPPLE),o.appendChild(r),l.appendChild(o),l.classList.add(this.CssClasses_.RIPPLE_EFFECT)}this.element_.classList.contains(this.CssClasses_.BOTTOM_LEFT)&&this.outline_.classList.add(this.CssClasses_.BOTTOM_LEFT),this.element_.classList.contains(this.CssClasses_.BOTTOM_RIGHT)&&this.outline_.classList.add(this.CssClasses_.BOTTOM_RIGHT),this.element_.classList.contains(this.CssClasses_.TOP_LEFT)&&this.outline_.classList.add(this.CssClasses_.TOP_LEFT),this.element_.classList.contains(this.CssClasses_.TOP_RIGHT)&&this.outline_.classList.add(this.CssClasses_.TOP_RIGHT),this.element_.classList.contains(this.CssClasses_.UNALIGNED)&&this.outline_.classList.add(this.CssClasses_.UNALIGNED),e.classList.add(this.CssClasses_.IS_UPGRADED)}},d.prototype.handleForClick_=function(e){if(this.element_&&this.forElement_){var t=this.forElement_.getBoundingClientRect(),s=this.forElement_.parentElement.getBoundingClientRect();this.element_.classList.contains(this.CssClasses_.UNALIGNED)||(this.element_.classList.contains(this.CssClasses_.BOTTOM_RIGHT)?(this.container_.style.right=s.right-t.right+"px",this.container_.style.top=this.forElement_.offsetTop+this.forElement_.offsetHeight+"px"):this.element_.classList.contains(this.CssClasses_.TOP_LEFT)?(this.container_.style.left=this.forElement_.offsetLeft+"px",this.container_.style.bottom=s.bottom-t.top+"px"):this.element_.classList.contains(this.CssClasses_.TOP_RIGHT)?(this.container_.style.right=s.right-t.right+"px",this.container_.style.bottom=s.bottom-t.top+"px"):(this.container_.style.left=this.forElement_.offsetLeft+"px",this.container_.style.top=this.forElement_.offsetTop+this.forElement_.offsetHeight+"px"))}this.toggle(e)},d.prototype.handleForKeyboardEvent_=function(e){if(this.element_&&this.container_&&this.forElement_){var t=this.element_.querySelectorAll("."+this.CssClasses_.ITEM+":not([disabled])");t&&t.length>0&&this.container_.classList.contains(this.CssClasses_.IS_VISIBLE)&&(e.keyCode===this.Keycodes_.UP_ARROW?(e.preventDefault(),t[t.length-1].focus()):e.keyCode===this.Keycodes_.DOWN_ARROW&&(e.preventDefault(),t[0].focus()))}},d.prototype.handleItemKeyboardEvent_=function(e){if(this.element_&&this.container_){var t=this.element_.querySelectorAll("."+this.CssClasses_.ITEM+":not([disabled])");if(t&&t.length>0&&this.container_.classList.contains(this.CssClasses_.IS_VISIBLE)){var s=Array.prototype.slice.call(t).indexOf(e.target);if(e.keyCode===this.Keycodes_.UP_ARROW)e.preventDefault(),s>0?t[s-1].focus():t[t.length-1].focus();else if(e.keyCode===this.Keycodes_.DOWN_ARROW)e.preventDefault(),t.length>s+1?t[s+1].focus():t[0].focus();else if(e.keyCode===this.Keycodes_.SPACE||e.keyCode===this.Keycodes_.ENTER){e.preventDefault();var i=new MouseEvent("mousedown");e.target.dispatchEvent(i),i=new MouseEvent("mouseup"),e.target.dispatchEvent(i),e.target.click()}else e.keyCode===this.Keycodes_.ESCAPE&&(e.preventDefault(),this.hide())}}},d.prototype.handleItemClick_=function(e){e.target.hasAttribute("disabled")?e.stopPropagation():(this.closing_=!0,window.setTimeout(function(e){this.hide(),this.closing_=!1}.bind(this),this.Constant_.CLOSE_TIMEOUT))},d.prototype.applyClip_=function(e,t){this.element_.classList.contains(this.CssClasses_.UNALIGNED)?this.element_.style.clip="":this.element_.classList.contains(this.CssClasses_.BOTTOM_RIGHT)?this.element_.style.clip="rect(0 "+t+"px 0 "+t+"px)":this.element_.classList.contains(this.CssClasses_.TOP_LEFT)?this.element_.style.clip="rect("+e+"px 0 "+e+"px 0)":this.element_.classList.contains(this.CssClasses_.TOP_RIGHT)?this.element_.style.clip="rect("+e+"px "+t+"px "+e+"px "+t+"px)":this.element_.style.clip=""},d.prototype.removeAnimationEndListener_=function(e){e.target.classList.remove(d.prototype.CssClasses_.IS_ANIMATING)},d.prototype.addAnimationEndListener_=function(){this.element_.addEventListener("transitionend",this.removeAnimationEndListener_),this.element_.addEventListener("webkitTransitionEnd",this.removeAnimationEndListener_)},d.prototype.show=function(e){if(this.element_&&this.container_&&this.outline_){var t=this.element_.getBoundingClientRect().height,s=this.element_.getBoundingClientRect().width;this.container_.style.width=s+"px",this.container_.style.height=t+"px",this.outline_.style.width=s+"px",this.outline_.style.height=t+"px";for(var i=this.Constant_.TRANSITION_DURATION_SECONDS*this.Constant_.TRANSITION_DURATION_FRACTION,n=this.element_.querySelectorAll("."+this.CssClasses_.ITEM),a=0;a<n.length;a++){var l=null;l=this.element_.classList.contains(this.CssClasses_.TOP_LEFT)||this.element_.classList.contains(this.CssClasses_.TOP_RIGHT)?(t-n[a].offsetTop-n[a].offsetHeight)/t*i+"s":n[a].offsetTop/t*i+"s",n[a].style.transitionDelay=l}this.applyClip_(t,s),window.requestAnimationFrame(function(){this.element_.classList.add(this.CssClasses_.IS_ANIMATING),this.element_.style.clip="rect(0 "+s+"px "+t+"px 0)",this.container_.classList.add(this.CssClasses_.IS_VISIBLE)}.bind(this)),this.addAnimationEndListener_();var o=function(t){t===e||this.closing_||t.target.parentNode===this.element_||(document.removeEventListener("click",o),this.hide())}.bind(this);document.addEventListener("click",o)}},d.prototype.show=d.prototype.show,d.prototype.hide=function(){if(this.element_&&this.container_&&this.outline_){for(var e=this.element_.querySelectorAll("."+this.CssClasses_.ITEM),t=0;t<e.length;t++)e[t].style.removeProperty("transition-delay");var s=this.element_.getBoundingClientRect(),i=s.height,n=s.width;this.element_.classList.add(this.CssClasses_.IS_ANIMATING),this.applyClip_(i,n),this.container_.classList.remove(this.CssClasses_.IS_VISIBLE),this.addAnimationEndListener_()}},d.prototype.hide=d.prototype.hide,d.prototype.toggle=function(e){this.container_.classList.contains(this.CssClasses_.IS_VISIBLE)?this.hide():this.show(e)},d.prototype.toggle=d.prototype.toggle,s.register({constructor:d,classAsString:"MaterialMenu",cssClass:"mdl-js-menu",widget:!0});var h=function(e){this.element_=e,this.init()};window.MaterialProgress=h,h.prototype.Constant_={},h.prototype.CssClasses_={INDETERMINATE_CLASS:"mdl-progress__indeterminate"},h.prototype.setProgress=function(e){this.element_.classList.contains(this.CssClasses_.INDETERMINATE_CLASS)||(this.progressbar_.style.width=e+"%")},h.prototype.setProgress=h.prototype.setProgress,h.prototype.setBuffer=function(e){this.bufferbar_.style.width=e+"%",this.auxbar_.style.width=100-e+"%"},h.prototype.setBuffer=h.prototype.setBuffer,h.prototype.init=function(){if(this.element_){var e=document.createElement("div");e.className="progressbar bar bar1",this.element_.appendChild(e),this.progressbar_=e,e=document.createElement("div"),e.className="bufferbar bar bar2",this.element_.appendChild(e),this.bufferbar_=e,e=document.createElement("div"),e.className="auxbar bar bar3",this.element_.appendChild(e),this.auxbar_=e,this.progressbar_.style.width="0%",this.bufferbar_.style.width="100%",this.auxbar_.style.width="0%",this.element_.classList.add("is-upgraded")}},s.register({constructor:h,classAsString:"MaterialProgress",cssClass:"mdl-js-progress",widget:!0});var c=function(e){this.element_=e,this.init()};window.MaterialRadio=c,c.prototype.Constant_={TINY_TIMEOUT:.001},c.prototype.CssClasses_={IS_FOCUSED:"is-focused",IS_DISABLED:"is-disabled",IS_CHECKED:"is-checked",IS_UPGRADED:"is-upgraded",JS_RADIO:"mdl-js-radio",RADIO_BTN:"mdl-radio__button",RADIO_OUTER_CIRCLE:"mdl-radio__outer-circle",RADIO_INNER_CIRCLE:"mdl-radio__inner-circle",RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE_CONTAINER:"mdl-radio__ripple-container",RIPPLE_CENTER:"mdl-ripple--center",RIPPLE:"mdl-ripple"},c.prototype.onChange_=function(e){for(var t=document.getElementsByClassName(this.CssClasses_.JS_RADIO),s=0;s<t.length;s++){var i=t[s].querySelector("."+this.CssClasses_.RADIO_BTN);i.getAttribute("name")===this.btnElement_.getAttribute("name")&&t[s].MaterialRadio.updateClasses_()}},c.prototype.onFocus_=function(e){this.element_.classList.add(this.CssClasses_.IS_FOCUSED)},c.prototype.onBlur_=function(e){this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},c.prototype.onMouseup_=function(e){this.blur_()},c.prototype.updateClasses_=function(){this.checkDisabled(),this.checkToggleState()},c.prototype.blur_=function(){window.setTimeout(function(){this.btnElement_.blur()}.bind(this),this.Constant_.TINY_TIMEOUT)},c.prototype.checkDisabled=function(){this.btnElement_.disabled?this.element_.classList.add(this.CssClasses_.IS_DISABLED):this.element_.classList.remove(this.CssClasses_.IS_DISABLED)},c.prototype.checkDisabled=c.prototype.checkDisabled,c.prototype.checkToggleState=function(){this.btnElement_.checked?this.element_.classList.add(this.CssClasses_.IS_CHECKED):this.element_.classList.remove(this.CssClasses_.IS_CHECKED)},c.prototype.checkToggleState=c.prototype.checkToggleState,c.prototype.disable=function(){this.btnElement_.disabled=!0,this.updateClasses_()},c.prototype.disable=c.prototype.disable,c.prototype.enable=function(){this.btnElement_.disabled=!1,this.updateClasses_()},c.prototype.enable=c.prototype.enable,c.prototype.check=function(){this.btnElement_.checked=!0,this.updateClasses_()},c.prototype.check=c.prototype.check,c.prototype.uncheck=function(){this.btnElement_.checked=!1,this.updateClasses_()},c.prototype.uncheck=c.prototype.uncheck,c.prototype.init=function(){if(this.element_){this.btnElement_=this.element_.querySelector("."+this.CssClasses_.RADIO_BTN),this.boundChangeHandler_=this.onChange_.bind(this),this.boundFocusHandler_=this.onChange_.bind(this),this.boundBlurHandler_=this.onBlur_.bind(this),this.boundMouseUpHandler_=this.onMouseup_.bind(this);var e=document.createElement("span");e.classList.add(this.CssClasses_.RADIO_OUTER_CIRCLE);var t=document.createElement("span");t.classList.add(this.CssClasses_.RADIO_INNER_CIRCLE),this.element_.appendChild(e),this.element_.appendChild(t);var s;if(this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT)){this.element_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS),s=document.createElement("span"),s.classList.add(this.CssClasses_.RIPPLE_CONTAINER),s.classList.add(this.CssClasses_.RIPPLE_EFFECT),s.classList.add(this.CssClasses_.RIPPLE_CENTER),s.addEventListener("mouseup",this.boundMouseUpHandler_);var i=document.createElement("span");i.classList.add(this.CssClasses_.RIPPLE),s.appendChild(i),this.element_.appendChild(s)}this.btnElement_.addEventListener("change",this.boundChangeHandler_),this.btnElement_.addEventListener("focus",this.boundFocusHandler_),this.btnElement_.addEventListener("blur",this.boundBlurHandler_),this.element_.addEventListener("mouseup",this.boundMouseUpHandler_),this.updateClasses_(),this.element_.classList.add(this.CssClasses_.IS_UPGRADED)}},s.register({constructor:c,classAsString:"MaterialRadio",cssClass:"mdl-js-radio",widget:!0});var p=function(e){this.element_=e,this.isIE_=window.navigator.msPointerEnabled,this.init()};window.MaterialSlider=p,p.prototype.Constant_={},p.prototype.CssClasses_={IE_CONTAINER:"mdl-slider__ie-container",SLIDER_CONTAINER:"mdl-slider__container",BACKGROUND_FLEX:"mdl-slider__background-flex",BACKGROUND_LOWER:"mdl-slider__background-lower",BACKGROUND_UPPER:"mdl-slider__background-upper",IS_LOWEST_VALUE:"is-lowest-value",IS_UPGRADED:"is-upgraded"},p.prototype.onInput_=function(e){this.updateValueStyles_()},p.prototype.onChange_=function(e){this.updateValueStyles_()},p.prototype.onMouseUp_=function(e){e.target.blur()},p.prototype.onContainerMouseDown_=function(e){if(e.target===this.element_.parentElement){e.preventDefault();var t=new MouseEvent("mousedown",{target:e.target,buttons:e.buttons,clientX:e.clientX,clientY:this.element_.getBoundingClientRect().y});this.element_.dispatchEvent(t)}},p.prototype.updateValueStyles_=function(){var e=(this.element_.value-this.element_.min)/(this.element_.max-this.element_.min);0===e?this.element_.classList.add(this.CssClasses_.IS_LOWEST_VALUE):this.element_.classList.remove(this.CssClasses_.IS_LOWEST_VALUE),this.isIE_||(this.backgroundLower_.style.flex=e,this.backgroundLower_.style.webkitFlex=e,this.backgroundUpper_.style.flex=1-e,this.backgroundUpper_.style.webkitFlex=1-e)},p.prototype.disable=function(){this.element_.disabled=!0},p.prototype.disable=p.prototype.disable,p.prototype.enable=function(){this.element_.disabled=!1},p.prototype.enable=p.prototype.enable,p.prototype.change=function(e){"undefined"!=typeof e&&(this.element_.value=e),this.updateValueStyles_()},p.prototype.change=p.prototype.change,p.prototype.init=function(){if(this.element_){if(this.isIE_){var e=document.createElement("div");e.classList.add(this.CssClasses_.IE_CONTAINER),this.element_.parentElement.insertBefore(e,this.element_),this.element_.parentElement.removeChild(this.element_),e.appendChild(this.element_)}else{var t=document.createElement("div");t.classList.add(this.CssClasses_.SLIDER_CONTAINER),this.element_.parentElement.insertBefore(t,this.element_),this.element_.parentElement.removeChild(this.element_),t.appendChild(this.element_);var s=document.createElement("div");s.classList.add(this.CssClasses_.BACKGROUND_FLEX),t.appendChild(s),this.backgroundLower_=document.createElement("div"),this.backgroundLower_.classList.add(this.CssClasses_.BACKGROUND_LOWER),s.appendChild(this.backgroundLower_),this.backgroundUpper_=document.createElement("div"),this.backgroundUpper_.classList.add(this.CssClasses_.BACKGROUND_UPPER),s.appendChild(this.backgroundUpper_)}this.boundInputHandler=this.onInput_.bind(this),this.boundChangeHandler=this.onChange_.bind(this),this.boundMouseUpHandler=this.onMouseUp_.bind(this),this.boundContainerMouseDownHandler=this.onContainerMouseDown_.bind(this),this.element_.addEventListener("input",this.boundInputHandler),this.element_.addEventListener("change",this.boundChangeHandler),this.element_.addEventListener("mouseup",this.boundMouseUpHandler),this.element_.parentElement.addEventListener("mousedown",this.boundContainerMouseDownHandler),this.updateValueStyles_(),this.element_.classList.add(this.CssClasses_.IS_UPGRADED)}},s.register({constructor:p,classAsString:"MaterialSlider",cssClass:"mdl-js-slider",widget:!0});var C=function(e){if(this.element_=e,this.textElement_=this.element_.querySelector("."+this.cssClasses_.MESSAGE),this.actionElement_=this.element_.querySelector("."+this.cssClasses_.ACTION),!this.textElement_)throw new Error("There must be a message element for a snackbar.");if(!this.actionElement_)throw new Error("There must be an action element for a snackbar.");this.active=!1,this.actionHandler_=void 0,this.message_=void 0,this.actionText_=void 0,this.queuedNotifications_=[],this.setActionHidden_(!0)};window.MaterialSnackbar=C,C.prototype.Constant_={ANIMATION_LENGTH:250},C.prototype.cssClasses_={SNACKBAR:"mdl-snackbar",MESSAGE:"mdl-snackbar__text",ACTION:"mdl-snackbar__action",ACTIVE:"mdl-snackbar--active"},C.prototype.displaySnackbar_=function(){this.element_.setAttribute("aria-hidden","true"),this.actionHandler_&&(this.actionElement_.textContent=this.actionText_,
this.actionElement_.addEventListener("click",this.actionHandler_),this.setActionHidden_(!1)),this.textElement_.textContent=this.message_,this.element_.classList.add(this.cssClasses_.ACTIVE),this.element_.setAttribute("aria-hidden","false"),setTimeout(this.cleanup_.bind(this),this.timeout_)},C.prototype.showSnackbar=function(e){if(void 0===e)throw new Error("Please provide a data object with at least a message to display.");if(void 0===e.message)throw new Error("Please provide a message to be displayed.");if(e.actionHandler&&!e.actionText)throw new Error("Please provide action text with the handler.");this.active?this.queuedNotifications_.push(e):(this.active=!0,this.message_=e.message,e.timeout?this.timeout_=e.timeout:this.timeout_=2750,e.actionHandler&&(this.actionHandler_=e.actionHandler),e.actionText&&(this.actionText_=e.actionText),this.displaySnackbar_())},C.prototype.showSnackbar=C.prototype.showSnackbar,C.prototype.checkQueue_=function(){this.queuedNotifications_.length>0&&this.showSnackbar(this.queuedNotifications_.shift())},C.prototype.cleanup_=function(){this.element_.classList.remove(this.cssClasses_.ACTIVE),setTimeout(function(){this.element_.setAttribute("aria-hidden","true"),this.textElement_.textContent="",Boolean(this.actionElement_.getAttribute("aria-hidden"))||(this.setActionHidden_(!0),this.actionElement_.textContent="",this.actionElement_.removeEventListener("click",this.actionHandler_)),this.actionHandler_=void 0,this.message_=void 0,this.actionText_=void 0,this.active=!1,this.checkQueue_()}.bind(this),this.Constant_.ANIMATION_LENGTH)},C.prototype.setActionHidden_=function(e){e?this.actionElement_.setAttribute("aria-hidden","true"):this.actionElement_.removeAttribute("aria-hidden")},s.register({constructor:C,classAsString:"MaterialSnackbar",cssClass:"mdl-js-snackbar",widget:!0});var u=function(e){this.element_=e,this.init()};window.MaterialSpinner=u,u.prototype.Constant_={MDL_SPINNER_LAYER_COUNT:4},u.prototype.CssClasses_={MDL_SPINNER_LAYER:"mdl-spinner__layer",MDL_SPINNER_CIRCLE_CLIPPER:"mdl-spinner__circle-clipper",MDL_SPINNER_CIRCLE:"mdl-spinner__circle",MDL_SPINNER_GAP_PATCH:"mdl-spinner__gap-patch",MDL_SPINNER_LEFT:"mdl-spinner__left",MDL_SPINNER_RIGHT:"mdl-spinner__right"},u.prototype.createLayer=function(e){var t=document.createElement("div");t.classList.add(this.CssClasses_.MDL_SPINNER_LAYER),t.classList.add(this.CssClasses_.MDL_SPINNER_LAYER+"-"+e);var s=document.createElement("div");s.classList.add(this.CssClasses_.MDL_SPINNER_CIRCLE_CLIPPER),s.classList.add(this.CssClasses_.MDL_SPINNER_LEFT);var i=document.createElement("div");i.classList.add(this.CssClasses_.MDL_SPINNER_GAP_PATCH);var n=document.createElement("div");n.classList.add(this.CssClasses_.MDL_SPINNER_CIRCLE_CLIPPER),n.classList.add(this.CssClasses_.MDL_SPINNER_RIGHT);for(var a=[s,i,n],l=0;l<a.length;l++){var o=document.createElement("div");o.classList.add(this.CssClasses_.MDL_SPINNER_CIRCLE),a[l].appendChild(o)}t.appendChild(s),t.appendChild(i),t.appendChild(n),this.element_.appendChild(t)},u.prototype.createLayer=u.prototype.createLayer,u.prototype.stop=function(){this.element_.classList.remove("is-active")},u.prototype.stop=u.prototype.stop,u.prototype.start=function(){this.element_.classList.add("is-active")},u.prototype.start=u.prototype.start,u.prototype.init=function(){if(this.element_){for(var e=1;e<=this.Constant_.MDL_SPINNER_LAYER_COUNT;e++)this.createLayer(e);this.element_.classList.add("is-upgraded")}},s.register({constructor:u,classAsString:"MaterialSpinner",cssClass:"mdl-js-spinner",widget:!0});var E=function(e){this.element_=e,this.init()};window.MaterialSwitch=E,E.prototype.Constant_={TINY_TIMEOUT:.001},E.prototype.CssClasses_={INPUT:"mdl-switch__input",TRACK:"mdl-switch__track",THUMB:"mdl-switch__thumb",FOCUS_HELPER:"mdl-switch__focus-helper",RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE_CONTAINER:"mdl-switch__ripple-container",RIPPLE_CENTER:"mdl-ripple--center",RIPPLE:"mdl-ripple",IS_FOCUSED:"is-focused",IS_DISABLED:"is-disabled",IS_CHECKED:"is-checked"},E.prototype.onChange_=function(e){this.updateClasses_()},E.prototype.onFocus_=function(e){this.element_.classList.add(this.CssClasses_.IS_FOCUSED)},E.prototype.onBlur_=function(e){this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},E.prototype.onMouseUp_=function(e){this.blur_()},E.prototype.updateClasses_=function(){this.checkDisabled(),this.checkToggleState()},E.prototype.blur_=function(){window.setTimeout(function(){this.inputElement_.blur()}.bind(this),this.Constant_.TINY_TIMEOUT)},E.prototype.checkDisabled=function(){this.inputElement_.disabled?this.element_.classList.add(this.CssClasses_.IS_DISABLED):this.element_.classList.remove(this.CssClasses_.IS_DISABLED)},E.prototype.checkDisabled=E.prototype.checkDisabled,E.prototype.checkToggleState=function(){this.inputElement_.checked?this.element_.classList.add(this.CssClasses_.IS_CHECKED):this.element_.classList.remove(this.CssClasses_.IS_CHECKED)},E.prototype.checkToggleState=E.prototype.checkToggleState,E.prototype.disable=function(){this.inputElement_.disabled=!0,this.updateClasses_()},E.prototype.disable=E.prototype.disable,E.prototype.enable=function(){this.inputElement_.disabled=!1,this.updateClasses_()},E.prototype.enable=E.prototype.enable,E.prototype.on=function(){this.inputElement_.checked=!0,this.updateClasses_()},E.prototype.on=E.prototype.on,E.prototype.off=function(){this.inputElement_.checked=!1,this.updateClasses_()},E.prototype.off=E.prototype.off,E.prototype.init=function(){if(this.element_){this.inputElement_=this.element_.querySelector("."+this.CssClasses_.INPUT);var e=document.createElement("div");e.classList.add(this.CssClasses_.TRACK);var t=document.createElement("div");t.classList.add(this.CssClasses_.THUMB);var s=document.createElement("span");if(s.classList.add(this.CssClasses_.FOCUS_HELPER),t.appendChild(s),this.element_.appendChild(e),this.element_.appendChild(t),this.boundMouseUpHandler=this.onMouseUp_.bind(this),this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT)){this.element_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS),this.rippleContainerElement_=document.createElement("span"),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CONTAINER),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_EFFECT),this.rippleContainerElement_.classList.add(this.CssClasses_.RIPPLE_CENTER),this.rippleContainerElement_.addEventListener("mouseup",this.boundMouseUpHandler);var i=document.createElement("span");i.classList.add(this.CssClasses_.RIPPLE),this.rippleContainerElement_.appendChild(i),this.element_.appendChild(this.rippleContainerElement_)}this.boundChangeHandler=this.onChange_.bind(this),this.boundFocusHandler=this.onFocus_.bind(this),this.boundBlurHandler=this.onBlur_.bind(this),this.inputElement_.addEventListener("change",this.boundChangeHandler),this.inputElement_.addEventListener("focus",this.boundFocusHandler),this.inputElement_.addEventListener("blur",this.boundBlurHandler),this.element_.addEventListener("mouseup",this.boundMouseUpHandler),this.updateClasses_(),this.element_.classList.add("is-upgraded")}},s.register({constructor:E,classAsString:"MaterialSwitch",cssClass:"mdl-js-switch",widget:!0});var m=function(e){this.element_=e,this.init()};window.MaterialTabs=m,m.prototype.Constant_={},m.prototype.CssClasses_={TAB_CLASS:"mdl-tabs__tab",PANEL_CLASS:"mdl-tabs__panel",ACTIVE_CLASS:"is-active",UPGRADED_CLASS:"is-upgraded",MDL_JS_RIPPLE_EFFECT:"mdl-js-ripple-effect",MDL_RIPPLE_CONTAINER:"mdl-tabs__ripple-container",MDL_RIPPLE:"mdl-ripple",MDL_JS_RIPPLE_EFFECT_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events"},m.prototype.initTabs_=function(){this.element_.classList.contains(this.CssClasses_.MDL_JS_RIPPLE_EFFECT)&&this.element_.classList.add(this.CssClasses_.MDL_JS_RIPPLE_EFFECT_IGNORE_EVENTS),this.tabs_=this.element_.querySelectorAll("."+this.CssClasses_.TAB_CLASS),this.panels_=this.element_.querySelectorAll("."+this.CssClasses_.PANEL_CLASS);for(var t=0;t<this.tabs_.length;t++)new e(this.tabs_[t],this);this.element_.classList.add(this.CssClasses_.UPGRADED_CLASS)},m.prototype.resetTabState_=function(){for(var e=0;e<this.tabs_.length;e++)this.tabs_[e].classList.remove(this.CssClasses_.ACTIVE_CLASS)},m.prototype.resetPanelState_=function(){for(var e=0;e<this.panels_.length;e++)this.panels_[e].classList.remove(this.CssClasses_.ACTIVE_CLASS)},m.prototype.init=function(){this.element_&&this.initTabs_()},s.register({constructor:m,classAsString:"MaterialTabs",cssClass:"mdl-js-tabs"});var L=function(e){this.element_=e,this.maxRows=this.Constant_.NO_MAX_ROWS,this.init()};window.MaterialTextfield=L,L.prototype.Constant_={NO_MAX_ROWS:-1,MAX_ROWS_ATTRIBUTE:"maxrows"},L.prototype.CssClasses_={LABEL:"mdl-textfield__label",INPUT:"mdl-textfield__input",IS_DIRTY:"is-dirty",IS_FOCUSED:"is-focused",IS_DISABLED:"is-disabled",IS_INVALID:"is-invalid",IS_UPGRADED:"is-upgraded"},L.prototype.onKeyDown_=function(e){var t=e.target.value.split("\n").length;13===e.keyCode&&t>=this.maxRows&&e.preventDefault()},L.prototype.onFocus_=function(e){this.element_.classList.add(this.CssClasses_.IS_FOCUSED)},L.prototype.onBlur_=function(e){this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},L.prototype.onReset_=function(e){this.updateClasses_()},L.prototype.updateClasses_=function(){this.checkDisabled(),this.checkValidity(),this.checkDirty(),this.checkFocus()},L.prototype.checkDisabled=function(){this.input_.disabled?this.element_.classList.add(this.CssClasses_.IS_DISABLED):this.element_.classList.remove(this.CssClasses_.IS_DISABLED)},L.prototype.checkDisabled=L.prototype.checkDisabled,L.prototype.checkFocus=function(){Boolean(this.element_.querySelector(":focus"))?this.element_.classList.add(this.CssClasses_.IS_FOCUSED):this.element_.classList.remove(this.CssClasses_.IS_FOCUSED)},L.prototype.checkFocus=L.prototype.checkFocus,L.prototype.checkValidity=function(){this.input_.validity&&(this.input_.validity.valid?this.element_.classList.remove(this.CssClasses_.IS_INVALID):this.element_.classList.add(this.CssClasses_.IS_INVALID))},L.prototype.checkValidity=L.prototype.checkValidity,L.prototype.checkDirty=function(){this.input_.value&&this.input_.value.length>0?this.element_.classList.add(this.CssClasses_.IS_DIRTY):this.element_.classList.remove(this.CssClasses_.IS_DIRTY)},L.prototype.checkDirty=L.prototype.checkDirty,L.prototype.disable=function(){this.input_.disabled=!0,this.updateClasses_()},L.prototype.disable=L.prototype.disable,L.prototype.enable=function(){this.input_.disabled=!1,this.updateClasses_()},L.prototype.enable=L.prototype.enable,L.prototype.change=function(e){this.input_.value=e||"",this.updateClasses_()},L.prototype.change=L.prototype.change,L.prototype.init=function(){if(this.element_&&(this.label_=this.element_.querySelector("."+this.CssClasses_.LABEL),this.input_=this.element_.querySelector("."+this.CssClasses_.INPUT),this.input_)){this.input_.hasAttribute(this.Constant_.MAX_ROWS_ATTRIBUTE)&&(this.maxRows=parseInt(this.input_.getAttribute(this.Constant_.MAX_ROWS_ATTRIBUTE),10),isNaN(this.maxRows)&&(this.maxRows=this.Constant_.NO_MAX_ROWS)),this.boundUpdateClassesHandler=this.updateClasses_.bind(this),this.boundFocusHandler=this.onFocus_.bind(this),this.boundBlurHandler=this.onBlur_.bind(this),this.boundResetHandler=this.onReset_.bind(this),this.input_.addEventListener("input",this.boundUpdateClassesHandler),this.input_.addEventListener("focus",this.boundFocusHandler),this.input_.addEventListener("blur",this.boundBlurHandler),this.input_.addEventListener("reset",this.boundResetHandler),this.maxRows!==this.Constant_.NO_MAX_ROWS&&(this.boundKeyDownHandler=this.onKeyDown_.bind(this),this.input_.addEventListener("keydown",this.boundKeyDownHandler));var e=this.element_.classList.contains(this.CssClasses_.IS_INVALID);this.updateClasses_(),this.element_.classList.add(this.CssClasses_.IS_UPGRADED),e&&this.element_.classList.add(this.CssClasses_.IS_INVALID),this.input_.hasAttribute("autofocus")&&(this.element_.focus(),this.checkFocus())}},s.register({constructor:L,classAsString:"MaterialTextfield",cssClass:"mdl-js-textfield",widget:!0});var I=function(e){this.element_=e,this.init()};window.MaterialTooltip=I,I.prototype.Constant_={},I.prototype.CssClasses_={IS_ACTIVE:"is-active",BOTTOM:"mdl-tooltip--bottom",LEFT:"mdl-tooltip--left",RIGHT:"mdl-tooltip--right",TOP:"mdl-tooltip--top"},I.prototype.handleMouseEnter_=function(e){var t=e.target.getBoundingClientRect(),s=t.left+t.width/2,i=t.top+t.height/2,n=-1*(this.element_.offsetWidth/2),a=-1*(this.element_.offsetHeight/2);this.element_.classList.contains(this.CssClasses_.LEFT)||this.element_.classList.contains(this.CssClasses_.RIGHT)?(s=t.width/2,0>i+a?(this.element_.style.top=0,this.element_.style.marginTop=0):(this.element_.style.top=i+"px",this.element_.style.marginTop=a+"px")):0>s+n?(this.element_.style.left=0,this.element_.style.marginLeft=0):(this.element_.style.left=s+"px",this.element_.style.marginLeft=n+"px"),this.element_.classList.contains(this.CssClasses_.TOP)?this.element_.style.top=t.top-this.element_.offsetHeight-10+"px":this.element_.classList.contains(this.CssClasses_.RIGHT)?this.element_.style.left=t.left+t.width+10+"px":this.element_.classList.contains(this.CssClasses_.LEFT)?this.element_.style.left=t.left-this.element_.offsetWidth-10+"px":this.element_.style.top=t.top+t.height+10+"px",this.element_.classList.add(this.CssClasses_.IS_ACTIVE)},I.prototype.handleMouseLeave_=function(){this.element_.classList.remove(this.CssClasses_.IS_ACTIVE)},I.prototype.init=function(){if(this.element_){var e=this.element_.getAttribute("for");e&&(this.forElement_=document.getElementById(e)),this.forElement_&&(this.forElement_.hasAttribute("tabindex")||this.forElement_.setAttribute("tabindex","0"),this.boundMouseEnterHandler=this.handleMouseEnter_.bind(this),this.boundMouseLeaveHandler=this.handleMouseLeave_.bind(this),this.forElement_.addEventListener("mouseenter",this.boundMouseEnterHandler,!1),this.forElement_.addEventListener("touchend",this.boundMouseEnterHandler,!1),this.forElement_.addEventListener("mouseleave",this.boundMouseLeaveHandler,!1),window.addEventListener("touchstart",this.boundMouseLeaveHandler))}},s.register({constructor:I,classAsString:"MaterialTooltip",cssClass:"mdl-tooltip"});var f=function(e){this.element_=e,this.init()};window.MaterialLayout=f,f.prototype.Constant_={MAX_WIDTH:"(max-width: 1024px)",TAB_SCROLL_PIXELS:100,MENU_ICON:"&#xE5D2;",CHEVRON_LEFT:"chevron_left",CHEVRON_RIGHT:"chevron_right"},f.prototype.Keycodes_={ENTER:13,ESCAPE:27,SPACE:32},f.prototype.Mode_={STANDARD:0,SEAMED:1,WATERFALL:2,SCROLL:3},f.prototype.CssClasses_={CONTAINER:"mdl-layout__container",HEADER:"mdl-layout__header",DRAWER:"mdl-layout__drawer",CONTENT:"mdl-layout__content",DRAWER_BTN:"mdl-layout__drawer-button",ICON:"material-icons",JS_RIPPLE_EFFECT:"mdl-js-ripple-effect",RIPPLE_CONTAINER:"mdl-layout__tab-ripple-container",RIPPLE:"mdl-ripple",RIPPLE_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",HEADER_SEAMED:"mdl-layout__header--seamed",HEADER_WATERFALL:"mdl-layout__header--waterfall",HEADER_SCROLL:"mdl-layout__header--scroll",FIXED_HEADER:"mdl-layout--fixed-header",OBFUSCATOR:"mdl-layout__obfuscator",TAB_BAR:"mdl-layout__tab-bar",TAB_CONTAINER:"mdl-layout__tab-bar-container",TAB:"mdl-layout__tab",TAB_BAR_BUTTON:"mdl-layout__tab-bar-button",TAB_BAR_LEFT_BUTTON:"mdl-layout__tab-bar-left-button",TAB_BAR_RIGHT_BUTTON:"mdl-layout__tab-bar-right-button",PANEL:"mdl-layout__tab-panel",HAS_DRAWER:"has-drawer",HAS_TABS:"has-tabs",HAS_SCROLLING_HEADER:"has-scrolling-header",CASTING_SHADOW:"is-casting-shadow",IS_COMPACT:"is-compact",IS_SMALL_SCREEN:"is-small-screen",IS_DRAWER_OPEN:"is-visible",IS_ACTIVE:"is-active",IS_UPGRADED:"is-upgraded",IS_ANIMATING:"is-animating",ON_LARGE_SCREEN:"mdl-layout--large-screen-only",ON_SMALL_SCREEN:"mdl-layout--small-screen-only"},f.prototype.contentScrollHandler_=function(){this.header_.classList.contains(this.CssClasses_.IS_ANIMATING)||(this.content_.scrollTop>0&&!this.header_.classList.contains(this.CssClasses_.IS_COMPACT)?(this.header_.classList.add(this.CssClasses_.CASTING_SHADOW),this.header_.classList.add(this.CssClasses_.IS_COMPACT),this.header_.classList.add(this.CssClasses_.IS_ANIMATING)):this.content_.scrollTop<=0&&this.header_.classList.contains(this.CssClasses_.IS_COMPACT)&&(this.header_.classList.remove(this.CssClasses_.CASTING_SHADOW),this.header_.classList.remove(this.CssClasses_.IS_COMPACT),this.header_.classList.add(this.CssClasses_.IS_ANIMATING)))},f.prototype.keyboardEventHandler_=function(e){e.keyCode===this.Keycodes_.ESCAPE&&this.toggleDrawer()},f.prototype.screenSizeHandler_=function(){this.screenSizeMediaQuery_.matches?this.element_.classList.add(this.CssClasses_.IS_SMALL_SCREEN):(this.element_.classList.remove(this.CssClasses_.IS_SMALL_SCREEN),this.drawer_&&(this.drawer_.classList.remove(this.CssClasses_.IS_DRAWER_OPEN),this.obfuscator_.classList.remove(this.CssClasses_.IS_DRAWER_OPEN)))},f.prototype.drawerToggleHandler_=function(e){if(e&&"keydown"===e.type){if(e.keyCode!==this.Keycodes_.SPACE&&e.keyCode!==this.Keycodes_.ENTER)return;e.preventDefault()}this.toggleDrawer()},f.prototype.headerTransitionEndHandler_=function(){this.header_.classList.remove(this.CssClasses_.IS_ANIMATING)},f.prototype.headerClickHandler_=function(){this.header_.classList.contains(this.CssClasses_.IS_COMPACT)&&(this.header_.classList.remove(this.CssClasses_.IS_COMPACT),this.header_.classList.add(this.CssClasses_.IS_ANIMATING))},f.prototype.resetTabState_=function(e){for(var t=0;t<e.length;t++)e[t].classList.remove(this.CssClasses_.IS_ACTIVE)},f.prototype.resetPanelState_=function(e){for(var t=0;t<e.length;t++)e[t].classList.remove(this.CssClasses_.IS_ACTIVE)},f.prototype.toggleDrawer=function(){var e=this.element_.querySelector("."+this.CssClasses_.DRAWER_BTN);this.drawer_.classList.toggle(this.CssClasses_.IS_DRAWER_OPEN),this.obfuscator_.classList.toggle(this.CssClasses_.IS_DRAWER_OPEN),this.drawer_.classList.contains(this.CssClasses_.IS_DRAWER_OPEN)?(this.drawer_.setAttribute("aria-hidden","false"),e.setAttribute("aria-expanded","true")):(this.drawer_.setAttribute("aria-hidden","true"),e.setAttribute("aria-expanded","false"))},f.prototype.toggleDrawer=f.prototype.toggleDrawer,f.prototype.init=function(){if(this.element_){var e=document.createElement("div");e.classList.add(this.CssClasses_.CONTAINER),this.element_.parentElement.insertBefore(e,this.element_),this.element_.parentElement.removeChild(this.element_),e.appendChild(this.element_);for(var s=this.element_.childNodes,i=s.length,n=0;i>n;n++){var a=s[n];a.classList&&a.classList.contains(this.CssClasses_.HEADER)&&(this.header_=a),a.classList&&a.classList.contains(this.CssClasses_.DRAWER)&&(this.drawer_=a),a.classList&&a.classList.contains(this.CssClasses_.CONTENT)&&(this.content_=a)}window.addEventListener("pageshow",function(e){e.persisted&&(this.element_.style.overflowY="hidden",requestAnimationFrame(function(){this.element_.style.overflowY=""}.bind(this)))}.bind(this),!1),this.header_&&(this.tabBar_=this.header_.querySelector("."+this.CssClasses_.TAB_BAR));var l=this.Mode_.STANDARD;if(this.header_&&(this.header_.classList.contains(this.CssClasses_.HEADER_SEAMED)?l=this.Mode_.SEAMED:this.header_.classList.contains(this.CssClasses_.HEADER_WATERFALL)?(l=this.Mode_.WATERFALL,this.header_.addEventListener("transitionend",this.headerTransitionEndHandler_.bind(this)),this.header_.addEventListener("click",this.headerClickHandler_.bind(this))):this.header_.classList.contains(this.CssClasses_.HEADER_SCROLL)&&(l=this.Mode_.SCROLL,e.classList.add(this.CssClasses_.HAS_SCROLLING_HEADER)),l===this.Mode_.STANDARD?(this.header_.classList.add(this.CssClasses_.CASTING_SHADOW),this.tabBar_&&this.tabBar_.classList.add(this.CssClasses_.CASTING_SHADOW)):l===this.Mode_.SEAMED||l===this.Mode_.SCROLL?(this.header_.classList.remove(this.CssClasses_.CASTING_SHADOW),this.tabBar_&&this.tabBar_.classList.remove(this.CssClasses_.CASTING_SHADOW)):l===this.Mode_.WATERFALL&&(this.content_.addEventListener("scroll",this.contentScrollHandler_.bind(this)),this.contentScrollHandler_())),this.drawer_){var o=this.element_.querySelector("."+this.CssClasses_.DRAWER_BTN);if(!o){o=document.createElement("div"),o.setAttribute("aria-expanded","false"),o.setAttribute("role","button"),o.setAttribute("tabindex","0"),o.classList.add(this.CssClasses_.DRAWER_BTN);var r=document.createElement("i");r.classList.add(this.CssClasses_.ICON),r.innerHTML=this.Constant_.MENU_ICON,o.appendChild(r)}this.drawer_.classList.contains(this.CssClasses_.ON_LARGE_SCREEN)?o.classList.add(this.CssClasses_.ON_LARGE_SCREEN):this.drawer_.classList.contains(this.CssClasses_.ON_SMALL_SCREEN)&&o.classList.add(this.CssClasses_.ON_SMALL_SCREEN),o.addEventListener("click",this.drawerToggleHandler_.bind(this)),o.addEventListener("keydown",this.drawerToggleHandler_.bind(this)),this.element_.classList.add(this.CssClasses_.HAS_DRAWER),this.element_.classList.contains(this.CssClasses_.FIXED_HEADER)?this.header_.insertBefore(o,this.header_.firstChild):this.element_.insertBefore(o,this.content_);var _=document.createElement("div");_.classList.add(this.CssClasses_.OBFUSCATOR),this.element_.appendChild(_),_.addEventListener("click",this.drawerToggleHandler_.bind(this)),this.obfuscator_=_,this.drawer_.addEventListener("keydown",this.keyboardEventHandler_.bind(this)),this.drawer_.setAttribute("aria-hidden","true")}if(this.screenSizeMediaQuery_=window.matchMedia(this.Constant_.MAX_WIDTH),this.screenSizeMediaQuery_.addListener(this.screenSizeHandler_.bind(this)),this.screenSizeHandler_(),this.header_&&this.tabBar_){this.element_.classList.add(this.CssClasses_.HAS_TABS);var d=document.createElement("div");d.classList.add(this.CssClasses_.TAB_CONTAINER),this.header_.insertBefore(d,this.tabBar_),this.header_.removeChild(this.tabBar_);var h=document.createElement("div");h.classList.add(this.CssClasses_.TAB_BAR_BUTTON),h.classList.add(this.CssClasses_.TAB_BAR_LEFT_BUTTON);var c=document.createElement("i");c.classList.add(this.CssClasses_.ICON),c.textContent=this.Constant_.CHEVRON_LEFT,h.appendChild(c),h.addEventListener("click",function(){this.tabBar_.scrollLeft-=this.Constant_.TAB_SCROLL_PIXELS}.bind(this));var p=document.createElement("div");p.classList.add(this.CssClasses_.TAB_BAR_BUTTON),p.classList.add(this.CssClasses_.TAB_BAR_RIGHT_BUTTON);var C=document.createElement("i");C.classList.add(this.CssClasses_.ICON),C.textContent=this.Constant_.CHEVRON_RIGHT,p.appendChild(C),p.addEventListener("click",function(){this.tabBar_.scrollLeft+=this.Constant_.TAB_SCROLL_PIXELS}.bind(this)),d.appendChild(h),d.appendChild(this.tabBar_),d.appendChild(p);var u=function(){this.tabBar_.scrollLeft>0?h.classList.add(this.CssClasses_.IS_ACTIVE):h.classList.remove(this.CssClasses_.IS_ACTIVE),this.tabBar_.scrollLeft<this.tabBar_.scrollWidth-this.tabBar_.offsetWidth?p.classList.add(this.CssClasses_.IS_ACTIVE):p.classList.remove(this.CssClasses_.IS_ACTIVE)}.bind(this);this.tabBar_.addEventListener("scroll",u),u(),this.tabBar_.classList.contains(this.CssClasses_.JS_RIPPLE_EFFECT)&&this.tabBar_.classList.add(this.CssClasses_.RIPPLE_IGNORE_EVENTS);for(var E=this.tabBar_.querySelectorAll("."+this.CssClasses_.TAB),m=this.content_.querySelectorAll("."+this.CssClasses_.PANEL),L=0;L<E.length;L++)new t(E[L],E,m,this)}this.element_.classList.add(this.CssClasses_.IS_UPGRADED)}},window.MaterialLayoutTab=t,s.register({constructor:f,classAsString:"MaterialLayout",cssClass:"mdl-js-layout"});var b=function(e){this.element_=e,this.init()};window.MaterialDataTable=b,b.prototype.Constant_={},b.prototype.CssClasses_={DATA_TABLE:"mdl-data-table",SELECTABLE:"mdl-data-table--selectable",SELECT_ELEMENT:"mdl-data-table__select",IS_SELECTED:"is-selected",IS_UPGRADED:"is-upgraded"},b.prototype.selectRow_=function(e,t,s){return t?function(){e.checked?t.classList.add(this.CssClasses_.IS_SELECTED):t.classList.remove(this.CssClasses_.IS_SELECTED)}.bind(this):s?function(){var t,i;if(e.checked)for(t=0;t<s.length;t++)i=s[t].querySelector("td").querySelector(".mdl-checkbox"),i.MaterialCheckbox.check(),s[t].classList.add(this.CssClasses_.IS_SELECTED);else for(t=0;t<s.length;t++)i=s[t].querySelector("td").querySelector(".mdl-checkbox"),i.MaterialCheckbox.uncheck(),s[t].classList.remove(this.CssClasses_.IS_SELECTED)}.bind(this):void 0},b.prototype.createCheckbox_=function(e,t){var i=document.createElement("label"),n=["mdl-checkbox","mdl-js-checkbox","mdl-js-ripple-effect",this.CssClasses_.SELECT_ELEMENT];i.className=n.join(" ");var a=document.createElement("input");return a.type="checkbox",a.classList.add("mdl-checkbox__input"),e?(a.checked=e.classList.contains(this.CssClasses_.IS_SELECTED),a.addEventListener("change",this.selectRow_(a,e))):t&&a.addEventListener("change",this.selectRow_(a,null,t)),i.appendChild(a),s.upgradeElement(i,"MaterialCheckbox"),i},b.prototype.init=function(){if(this.element_){var e=this.element_.querySelector("th"),t=Array.prototype.slice.call(this.element_.querySelectorAll("tbody tr")),s=Array.prototype.slice.call(this.element_.querySelectorAll("tfoot tr")),i=t.concat(s);if(this.element_.classList.contains(this.CssClasses_.SELECTABLE)){var n=document.createElement("th"),a=this.createCheckbox_(null,i);n.appendChild(a),e.parentElement.insertBefore(n,e);for(var l=0;l<i.length;l++){var o=i[l].querySelector("td");if(o){var r=document.createElement("td");if("TBODY"===i[l].parentNode.nodeName.toUpperCase()){var _=this.createCheckbox_(i[l]);r.appendChild(_)}i[l].insertBefore(r,o)}}this.element_.classList.add(this.CssClasses_.IS_UPGRADED)}}},s.register({constructor:b,classAsString:"MaterialDataTable",cssClass:"mdl-js-data-table"});var y=function(e){this.element_=e,this.init()};window.MaterialRipple=y,y.prototype.Constant_={INITIAL_SCALE:"scale(0.0001, 0.0001)",INITIAL_SIZE:"1px",INITIAL_OPACITY:"0.4",FINAL_OPACITY:"0",FINAL_SCALE:""},y.prototype.CssClasses_={RIPPLE_CENTER:"mdl-ripple--center",RIPPLE_EFFECT_IGNORE_EVENTS:"mdl-js-ripple-effect--ignore-events",RIPPLE:"mdl-ripple",IS_ANIMATING:"is-animating",IS_VISIBLE:"is-visible"},y.prototype.downHandler_=function(e){if(!this.rippleElement_.style.width&&!this.rippleElement_.style.height){var t=this.element_.getBoundingClientRect();this.boundHeight=t.height,this.boundWidth=t.width,this.rippleSize_=2*Math.sqrt(t.width*t.width+t.height*t.height)+2,this.rippleElement_.style.width=this.rippleSize_+"px",this.rippleElement_.style.height=this.rippleSize_+"px"}if(this.rippleElement_.classList.add(this.CssClasses_.IS_VISIBLE),"mousedown"===e.type&&this.ignoringMouseDown_)this.ignoringMouseDown_=!1;else{"touchstart"===e.type&&(this.ignoringMouseDown_=!0);var s=this.getFrameCount();if(s>0)return;this.setFrameCount(1);var i,n,a=e.currentTarget.getBoundingClientRect();if(0===e.clientX&&0===e.clientY)i=Math.round(a.width/2),n=Math.round(a.height/2);else{var l=e.clientX?e.clientX:e.touches[0].clientX,o=e.clientY?e.clientY:e.touches[0].clientY;i=Math.round(l-a.left),n=Math.round(o-a.top)}this.setRippleXY(i,n),this.setRippleStyles(!0),window.requestAnimationFrame(this.animFrameHandler.bind(this))}},y.prototype.upHandler_=function(e){e&&2!==e.detail&&window.setTimeout(function(){this.rippleElement_.classList.remove(this.CssClasses_.IS_VISIBLE)}.bind(this),0)},y.prototype.init=function(){if(this.element_){var e=this.element_.classList.contains(this.CssClasses_.RIPPLE_CENTER);this.element_.classList.contains(this.CssClasses_.RIPPLE_EFFECT_IGNORE_EVENTS)||(this.rippleElement_=this.element_.querySelector("."+this.CssClasses_.RIPPLE),this.frameCount_=0,this.rippleSize_=0,this.x_=0,this.y_=0,this.ignoringMouseDown_=!1,this.boundDownHandler=this.downHandler_.bind(this),this.element_.addEventListener("mousedown",this.boundDownHandler),this.element_.addEventListener("touchstart",this.boundDownHandler),this.boundUpHandler=this.upHandler_.bind(this),this.element_.addEventListener("mouseup",this.boundUpHandler),this.element_.addEventListener("mouseleave",this.boundUpHandler),this.element_.addEventListener("touchend",this.boundUpHandler),this.element_.addEventListener("blur",this.boundUpHandler),this.getFrameCount=function(){return this.frameCount_},this.setFrameCount=function(e){this.frameCount_=e},this.getRippleElement=function(){return this.rippleElement_},this.setRippleXY=function(e,t){this.x_=e,this.y_=t},this.setRippleStyles=function(t){if(null!==this.rippleElement_){var s,i,n,a="translate("+this.x_+"px, "+this.y_+"px)";t?(i=this.Constant_.INITIAL_SCALE,n=this.Constant_.INITIAL_SIZE):(i=this.Constant_.FINAL_SCALE,n=this.rippleSize_+"px",e&&(a="translate("+this.boundWidth/2+"px, "+this.boundHeight/2+"px)")),s="translate(-50%, -50%) "+a+i,this.rippleElement_.style.webkitTransform=s,this.rippleElement_.style.msTransform=s,this.rippleElement_.style.transform=s,t?this.rippleElement_.classList.remove(this.CssClasses_.IS_ANIMATING):this.rippleElement_.classList.add(this.CssClasses_.IS_ANIMATING)}},this.animFrameHandler=function(){this.frameCount_-- >0?window.requestAnimationFrame(this.animFrameHandler.bind(this)):this.setRippleStyles(!1)})}},s.register({constructor:y,classAsString:"MaterialRipple",cssClass:"mdl-js-ripple-effect",widget:!1})}();
//# sourceMappingURL=material.min.js.map

//# sourceMappingURL=scripts.js.map
