<?php

class Prospects_CustomLogic
{
    function associatePerson(&$bean, $event, $arguments)
    {
        if($bean->syncWithContact != 1) {
        	if(empty($bean->contact_id_c)) {
        		$contact_bean = BeanFactory::newBean('Contacts');
        		$contact_bean->new_with_id = true;
        		$contact_bean->id = create_guid();
        		$contact_bean->first_name = $bean->first_name;
        		$contact_bean->last_name = $bean->last_name;
        		$contact_bean->title = $bean->title;
        		$contact_bean->website_c = $bean->website_c;
                $contact_bean->phone_mobile = $bean->phone_mobile;
                $contact_bean->linkedin_c = $bean->linkedin_c;
        		$contact_bean->prospect_id_c = $bean->id;
                $contact_bean->contact_stage_c = "target";
                $contact_bean->description = "";
        		$contact_bean->syncWithContact = 1;

        		for($i = 1; $i < 10; $i++) {
        			if(!empty($bean->{'email'.$i})) {
        				$contact_bean->{'email'.$i} = $bean->{'email'.$i};
        			} else {
        				break;
        			}
        		}

                $contact_bean->load_relationship('accounts_contacts');
                $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_bean->id ,'account_id' => $bean->accounts_prospects_1accounts_ida), true, true);

                $bean->contact_id_c = $contact_bean->id;

        		$contact_bean->save();
                unset($contact_bean);
        	} else {
                $contact_bean = BeanFactory::getBean('Contacts', $bean->contact_id_c);
                $contact_bean->first_name = $bean->first_name;
                $contact_bean->last_name = $bean->last_name;
                $contact_bean->title = $bean->title;
                $contact_bean->website_c = $bean->website_c;
                $contact_bean->phone_mobile = $bean->phone_mobile;
                $contact_bean->linkedin_c = $bean->linkedin_c;
                $contact_bean->prospect_id_c = $bean->id;
                $contact_bean->syncWithContact = 1;

                if($contact_bean->contact_stage_c == "contact" || $contact_bean->contact_stage_c == "") {
                    $contact_bean->contact_stage_c = "target";
                }

                for($i = 1; $i < 10; $i++) {
                    if(!empty($bean->{'email'.$i})) {
                        $contact_bean->{'email'.$i} = $bean->{'email'.$i};
                    } else {
                        break;
                    }
                }

                $contact_bean->load_relationship('accounts_contacts');
                $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $bean->contact_id_c ,'account_id' => $bean->accounts_prospects_1accounts_ida), true, true);

                $contact_bean->save();
                unset($contact_bean);
            }
        }

        unset($bean->syncWithContact);
    }

    function businessLogic(&$bean, $event, $arguments)
    {
        $choice = str_replace("^", "", $bean->client_for_c);
        $choice = split(",", $choice);

        foreach(array("rsc_c", "pkig_c", "ats_c", "rwt_c", "rdsg_c") as $value) {
            $bean->{$value} = 0;
        }

        foreach($choice as $key => $value) {
            $company = strtolower($value) ."_c";

            $bean->{$company} = 1;
        }

        unset($bean->syncWithContact);
    }
}