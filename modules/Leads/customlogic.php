<?php

class Leads_CustomLogic
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
                $contact_bean->website_c = $bean->website;
                $contact_bean->phone_mobile = $bean->phone_mobile;
                $contact_bean->linkedin_c = $bean->linkedin_c;
                $contact_bean->lead_id_c = $bean->id;
                $contact_bean->contact_stage_c = "lead";
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
                $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $contact_bean->id ,'account_id' => $bean->accounts_leads_1accounts_ida), true, true);

                $contact_bean->save();
                unset($contact_bean);
            } else {
                $contact_bean = BeanFactory::getBean('Contacts', $bean->contact_id_c);
                $contact_bean->first_name = $bean->first_name;
                $contact_bean->last_name = $bean->last_name;
                $contact_bean->title = $bean->title;
                $contact_bean->website_c = $bean->website;
                $contact_bean->phone_mobile = $bean->phone_mobile;
                $contact_bean->linkedin_c = $bean->linkedin_c;
                $contact_bean->contact_stage_c = "lead";
                $contact_bean->lead_id_c = $bean->id;
                $contact_bean->syncWithContact = 1;

                for($i = 1; $i < 10; $i++) {
                    if(!empty($bean->{'email'.$i})) {
                        $contact_bean->{'email'.$i} = $bean->{'email'.$i};
                    } else {
                        break;
                    }
                }

                $contact_bean->load_relationship('accounts_contacts');
                $contact_bean->set_relationship('accounts_contacts', array('contact_id' => $bean->contact_id_c ,'account_id' => $bean->accounts_leads_1accounts_ida), true, true);

                $contact_bean->save();
                unset($contact_bean);
            }
        }

        unset($bean->syncWithContact);
    }

    function businessLogic(&$bean, $event, $arguments)
    {
        if($arguments['isUpdate'] == 1) {
            if(!empty($bean->prospect_id_c)) {
                $target_bean = BeanFactory::getBean("Prospects", $bean->prospect_id_c);
                $target_bean->syncWithContact = 1;

                if(!$target_bean->lead_was_created_c) {
                    $target_bean->lead_was_created_c = 1;
                    $target_bean->save();
                }

                unset($target_bean);
            }
        }

        unset($bean->syncWithContact);
    }
}