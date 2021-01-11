<?xml version="1.0"?>
<ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:sdtc="urn:hl7-org:sdtc">
    <realmCode code="US"/>
    <typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/>
    <templateId root="2.16.840.1.113883.10.20.22.1.1"/>
    <templateId root="2.16.840.1.113883.10.20.22.1.2"/>
    <id root="3c9190a8-2020-b79c-4089-001A64958C30"/>
    <code code="81214-9" codeSystem="2.16.840.1.113883.6.1" displayName="Continuity of Care Document"/>
    <title>Ambulatory Summary</title>
    <effectiveTime value="{{now()->format('YmdhsiO')}}"/>
    <confidentialityCode code="N" codeSystem="2.16.840.1.113883.5.25"/>
    <languageCode code="en-US"/>
    <recordTarget>
        <patientRole>
            <id root="2.16.840.1.113883.3.564.16521" extension="{{$mrn}}"/>
            <id root="2.16.840.1.113883.4.1" extension="{{$mrn}}"/>
            <addr use="HP">
                <streetAddressLine>{{$street}}</streetAddressLine>
                <streetAddressLine>{{$street2}}</streetAddressLine>
                <city>{{$city}}</city>
                <state>{{$state}}</state>
                <postalCode>{{$zip}}</postalCode>
                <country>US</country>
            </addr>
            @if($homePhone)
                <telecom use="HP" value="tel:{{$homePhone}}"/>
            @endif
            @if($workPhone)
                <telecom use="WP" value="tel:{{$workPhone}}"/>
            @endif
            @if($cellPhone)
                <telecom use="MC" value="tel:{{$cellPhone}}"/>
            @endif
            @if($email)
                <telecom use="HP" value="mailto:{{$email}}"/>
            @endif
            <patient>
                <name>
                    <given>{{$firstName}}</given>
                    <family>{{$lastName}}</family>
                </name>
                <administrativeGenderCode code="F" codeSystem="2.16.840.1.113883.5.1"/>
                <birthTime value="{{$dob}}"/>
                @if($ethnicity)
                    <raceCode code="{{$raceCode}}" codeSystem="2.16.840.1.113883.6.238" displayName="{{$ethnicity}}"/>
                @endif
                <languageCommunication>
                    <languageCode code="{{$language}}"/>
                </languageCommunication>
            </patient>
        </patientRole>
    </recordTarget>
    <author>
        <time value="20201016172712-0400"/>
        <assignedAuthor>
            <id root="2.16.840.1.113883.3.564"/>
            <addr use="WP">
                <streetAddressLine></streetAddressLine>
                <streetAddressLine nullFlavor="NI"/>
                <city></city>
                <state></state>
                <postalCode></postalCode>
                <country>US</country>
            </addr>
            <telecom use="WP" value=""/>
            <assignedAuthoringDevice>
                <manufacturerModelName>careplanmanager</manufacturerModelName>
                <softwareName>careplanmanager</softwareName>
            </assignedAuthoringDevice>
        </assignedAuthor>
    </author>
    <custodian>
        <assignedCustodian>
            <representedCustodianOrganization>
                <id root="2.16.840.1.113883.3.564"/>
                <name>athenahealth</name>
                <telecom use="WP" value=""/>
                <addr use="WP">
                    <streetAddressLine></streetAddressLine>
                    <streetAddressLine nullFlavor="NI"/>
                    <city></city>
                    <state></state>
                    <postalCode></postalCode>
                    <country>US</country>
                </addr>
            </representedCustodianOrganization>
        </assignedCustodian>
    </custodian>
</ClinicalDocument>
