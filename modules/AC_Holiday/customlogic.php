<?php

class AC_Holiday_Customlogic
{
    const JJ = '144c39bf-ccc3-65ec-2023-5407f7975b91';
    const AW = 'e07026a9-691a-67e7-32a6-5407f619ae5b';
    const ME = 'ada95982-6143-43d9-e3ae-540f494996bf';
    private $db;

    function __construct()
    {
        $this->db = DBManagerFactory::getInstance();
    }

    function getUserTasks($user_id, $from, $to)
    {
        $query = "SELECT name FROM tasks WHERE assigned_user_id='".$user_id."' AND date_due BETWEEN '".$from."' AND '".$to."' AND (deleted=0 AND status!='Completed') ";
        $request = $this->db->query($query);

        $tasks = '';
        while ( $row = $this->db->fetchByAssoc($request) ) {
            $tasks .= $row['name']."\n";
        }

        return $tasks; // zwraca zadania użytkownika
    }

    function getDevTeam($user_id)
    {
        $query = $this->db->query("SELECT id FROM users WHERE `reports_to_id` = '". self::AW ."' AND id = '{$user_id}' AND id NOT LIKE '144c39bf-ccc3-65ec-2023-5407f7975b91'");

        if($this->db->getRowCount($query) > 0) {
            return true;
        }

        return false;
    }

    function getUserInformations($user_id, $ME = false)
    {
        if($ME == true) { $result = $this->db->query('SELECT id, first_name, last_name, reports_to_id, COUNT(reports_to_id) AS ile, gender_c FROM users INNER JOIN users_cstm ON(id = id_c) WHERE id = "'. $user_id .'" AND aa_departments_id_c NOT LIKE "8b636633-dae5-6af9-15ae-56a5b82126b7"'); }
        else { $result = $this->db->query('SELECT id, first_name, last_name, reports_to_id, gender_c FROM users INNER JOIN users_cstm ON(id = id_c) WHERE id="'. $user_id .'" '); }

        return $this->db->fetchByAssoc($result); // zwraca użytkownika: id, imię, nazwisko, id przełożonego
    }

    function getUserClass($user_id)
    {
        $user_class = 0; // 0 - zwykły pracownik, 1 - team leader, 2 - administracja, 3 - zarząd

        $request = $this->db->query("SELECT id FROM acl_roles_users WHERE role_id LIKE '343cd46d-99d7-a4e3-a7a3-56ab19e4a8db'AND user_id LIKE '{$user_id}'");
        if($this->db->getRowCount($request) > 0)  $user_class = 1; // team leader

        $request = $this->db->query("SELECT id FROM team_memberships WHERE user_id = '{$user_id}' AND team_id IN (SELECT id FROM teams WHERE description LIKE 'FinAdm' OR name LIKE 'Finance & Administration Department')");
        if($this->db->getRowCount($request) > 0)  $user_class = 2; // administracja

        $request = $this->db->query("SELECT id FROM team_memberships WHERE user_id = '{$user_id}' AND team_id IN (SELECT id FROM teams WHERE description LIKE 'Board' OR name LIKE 'Board')");
        if($this->db->getRowCount($request) > 0)  $user_class = 3; // zarząd

        return $user_class; // zwraca klasę użytkownika
    }

    function getUserTeam($user_id)
    {
        $assignedResponsible = $this->db->query("SELECT t.*
                FROM teams t
                WHERE t.associated_user_id ='{$user_id}' AND t.private=1 AND t.deleted = 0 ");
        $teams = $this->db->fetchByAssoc($assignedResponsible);
        return $teams['id'];
    }

    function updateAssents($supervisor, $fa, $board, &$bean)
    {
        // zaakceptowali:
        if($supervisor == true && $fa == false && $board == false) { $this->db->query('UPDATE ac_holiday SET supervisor = 1 WHERE id = "'. $bean->id .'"'); } // tylko przełożony
        if($fa == true && $supervisor == false && $board == false) { $this->db->query('UPDATE ac_holiday_cstm SET fa_c = 1 WHERE id_c = "'. $bean->id .'"'); } // tylko administracja
        if($supervisor == true && $fa == true && $board == false) { $this->db->query('UPDATE ac_holiday LEFT JOIN ac_holiday_cstm ON(ac_holiday.id = ac_holiday_cstm.id_c) SET ac_holiday.supervisor = 1, ac_holiday_cstm.fa_c = 1 WHERE id = "'. $bean->id .'"'); } // przełożony i administracja
        if($supervisor == true && $board == true && $fa == false) { $this->db->query('UPDATE ac_holiday SET supervisor = 1, board = 1 WHERE id = "'. $bean->id .'"'); } // przełożony i zarząd
        if($supervisor == true && $board == true && $fa == true) { $this->db->query('UPDATE ac_holiday LEFT JOIN ac_holiday_cstm ON(ac_holiday.id = ac_holiday_cstm.id_c) SET ac_holiday.supervisor = 1, ac_holiday_cstm.fa_c = 1, ac_holiday.board = 1 WHERE id = "'. $bean->id .'"'); } // wszyscy święci
    }

    function setResponse($rejectedByUser, $agreement, $full_name = "")
    {
        // odrzucenie wniosku przez przełożonego
        if($rejectedByUser == 1) {
            if($agreement == 1) { $notifName = $full_name .' rejected your holiday request'; }
            else { $notifName = $full_name .' rejected your absence request'; }
        } elseif ($rejectedByUser == 0) { // odrzucenie wniosku z powodu braku dni wolnych
            if( $user_aggrement == 1 ) { $notifName = "Your holiday request was rejected, no free days."; }
            else { $notifName = "Your absence request was rejected, no free days."; }
        } else { // przyjęcie wniosku urlopowego
            if( $agreement == 1 ) { $notifName = "Your holiday request was accepted"; }
            else { $notifName = "Your absence request was accepted"; }
        }

        return $notifName; // zwraca nazwę notyfikacji
    }

    function setBoundary(&$bean, &$notif, $user_agreement)
    {
        $days_bilans_this_year = 0;
        $days_bilans_next_year = 0;

        // wczytanie dni wolnych, wykorzystanych
        $sql_holidays = $this->db->query("SELECT id_c, overdue_free_days_c, used_free_days_c, new_year_free_days_c FROM users_cstm WHERE id_c='". $bean->assigned_user_id ."' ");
        $boundary = $this->db->fetchByAssoc($sql_holidays);

        $used_days = intval($boundary['used_free_days_c']) + intval($bean->days_hd); // użyte dni wolne + ilość dni w wniosku

        // $GLOBALS['log']->error("Uzyte dni: ". $used_days);
        // $GLOBALS['log']->error("Wolne dni: ". intval($boundary['overdue_free_days_c']));
        // $GLOBALS['log']->error("Wzięte dni: ". intval($bean->days_hd));

        $GLOBALS['log']->fatal("----------------------------------------------------------");
        $GLOBALS['log']->fatal("Data początku: ". date("j-m-Y", strtotime($bean->v_from)));

        if(date("Y") < date("Y", strtotime($bean->v_from)) || date("Y") < date("Y", strtotime($bean->v_to))) {
            // np. v_from = 23.12.2016, a v_to = 3.01.2017
            if(date("m", strtotime($bean->v_to)) < date("m", strtotime($bean->v_from))) {
                $this_year_days = 0;

                for($i = date("j", strtotime($bean->v_from)); $i <= 31; $i++) {
                    $day_of_week = date('w', mktime(0, 0, 0, date("m"), $i, date("Y")));

                    if( !(in_array($day_of_week, array(0, 6)) || in_array($i, array(25, 26)))) {
                        $this_year_days++;
                    }
                }

                $days_bilans_this_year = intval($boundary['overdue_free_days_c']) - $this_year_days; // ile pozostało na ten rok
                $days_bilans_next_year = intval($boundary['new_year_free_days_c']) - (intval($bean->days_hd) - $this_year_days); // ile pozostało na rok następny
                $used_days = intval($boundary['used_free_days_c']) + $this_year_days; // użyte na ten rok + z urlopu na ten rok
            } else { // ten sam miesiąc, ale w nowym roku
                $days_bilans_next_year = intval($boundary['new_year_free_days_c']) - intval($bean->days_hd); // ile pozostało na rok następny
                $days_bilans_this_year = intval($boundary['overdue_free_days_c']); // ile pozostało na ten rok
                $used_days = intval($boundary['used_free_days_c']); // tylko użyte z tego roku
            }
        } else { // urlop wzięty na bierzący rok
            $days_bilans_this_year = intval($boundary['overdue_free_days_c']) - intval($bean->days_hd); // ile dni pozostało
            $days_bilans_next_year = intval($boundary['new_year_free_days_c']); // ile pozostało na rok następny
        }

        // ilość dni pozostałych mniejsz od zera - odrzucenie wniosku
        if($days_bilans_this_year < 0 || $days_bilans_next_year < 0) {
            $this->db->query("UPDATE ac_holiday_cstm SET rejected_c = 1 WHERE id_c = '". $bean->id ."'");
            $notifName = $this->setResponse(0, $user_agreement);
            $notif->deleted = 0;
        } else {
            // odebranie dni urlopowych
            if($bean->withdrawal_of_leave_c == 1){
                $notifName = "Your withdrawn days request was accepted";
                $notif->confirmation = 1;
            }else{
                // zaktualizowanie dni wolnych użytkownika
                $notifName = $this->setResponse(-1, $user_agreement);
                $this->db->query("UPDATE users_cstm SET overdue_free_days_c='".$days_bilans_this_year."', used_free_days_c='".$used_days."', new_year_free_days_c='". $days_bilans_next_year ."' WHERE id_c='".$bean->assigned_user_id."' ");
            }
        }

        $notif->name = $notifName;
        $notif->assigned_user_id = $bean->assigned_user_id; // notyfikacja zwracana do przypisanej osoby
    }

    function manageNotifications(&$bean, $event, $arguments)
    {
        $new_notification = 1;
        global $current_user;

        $sql_notification = $this->db->query('SELECT id FROM notifications WHERE parent_id = "'. $bean->id .'"');
        $notifications_id = $this->db->fetchByAssoc($sql_notification);
        $row = $this->getUserInformations($bean->assigned_user_id);
        $reports_to = $row['reports_to_id']; // przełożony użytkownika

        // prywatne zespoły:
        $main_assigned_team = $this->getUserTeam($bean->created_by); // składającego wniosek
        $assigned_teams[] = $this->getUserTeam($reports_to); // przełożonego
        $assigned_teams[] = $this->getUserTeam(self::ME); // Madzi
        $assigned_teams[] = $this->getUserTeam(self::JJ); // Jakuba
        $assigned_teams[] = $this->getUserTeam(self::AW); // Artura

        $GLOBALS['log']->fatal("-----------------------------------");
        $GLOBALS['log']->fatal("Zespoły:");
        $GLOBALS['log']->fatal(print_r($assigned_teams, true));
        $GLOBALS['log']->fatal(print_r($main_assigned_team, true));

        $bean->load_relationship('teams');
        $bean->team_id = $main_assigned_team;
        $GLOBALS['log']->fatal(print_r($bean->teams, true));
        @$bean->teams->replace($assigned_teams);

        if($this->db->getRowCount($sql_notification) > 0)  $new_notification = 0;

        if($new_notification == 1) {
            if($bean->import == false) {
                $notif = BeanFactory::newBean('Notifications');
                $this->addNotification($bean, $event, $arguments, $current_user, $notif);
            }
        } else {
            if($bean->import == false) {
                $notif = BeanFactory::getBean('Notifications', $notifications_id['id']);
                $this->editNotification($bean, $event, $arguments, $current_user, $notif);
            }
        }
    }

    function addNotification(&$bean, $event, $arguments, $current_user, $notif)
    {
        $set_name = true;
        $user_agreement = 0; // umowa zlecenie

        $sql_agreement = $this->db->query('SELECT aggrement_c FROM users_cstm WHERE id_c="'. $bean->assigned_user_id.'" AND aggrement_c LIKE "umowa_o_prace%"');
        if($this->db->getRowCount($sql_agreement) > 0 ) { $user_agreement = 1; } // umowa o pracę

        $row = $this->getUserInformations($bean->assigned_user_id); // dane użytkownika
        $reports_to = $row['reports_to_id']; // przełożony użytkownika
        $full_name = $row['first_name'] .' '. $row['last_name'];  /// pełne imie i nazwisko użytkownika
        $gender = ($row['gender_c'] == "male") ? 'his' : 'her';

        $notify_name = $full_name .' has sick leave';
        $dev_team = $this->getDevTeam($bean->assigned_user_id);
        $current_user_class = ($dev_team == true) ? 1 : $this->getUserClass($current_user->id);
        $assigned_user_class = ($dev_team == true) ? 1 : $this->getUserClass($bean->assigned_user_id);

        // $GLOBALS['log']->fatal("Dev team: ". $dev_team);
        // $GLOBALS['log']->fatal("Assigned user class: ". $assigned_user_class);
        // $GLOBALS['log']->fatal("Current user class: ". $current_user_class);

        // jest urlop zdrowotny
        if($bean->sick_leave) {
            if($bean->withdrawal_of_leave_c) { $notify_name = $full_name .' has withdrawn '. $gender .' free days'; } // odebranie dni wolnych

            // zwykły użytkownik
            if($assigned_user_class == 0) {
                $notif->assigned_user_id = $reports_to; // notyfikacja dla przełożonego
                $this->updateAssents(false, true, false, $bean); // automatycznie idzie akceptacja Madzi
            } elseif(($assigned_user_class == 1) || ($assigned_user_class == 2)) { // team leader lub dział administracji
                $notif->assigned_user_id = ($dev_team == true) ? self::AW : self::JJ; // notyfikacja dla Jakuba, jeżeli DEV to dla Artura
                $this->updateAssents(true, true, false, $bean); // automatycznie idzie akceptacja Madzi i team leadera
            } else { // zarząd
                $this->updateAssents(true, true, true, $bean); // automatycznie idzie akceptacja wszystkich świętych
                $notify_name = "Your sick leave was accepted";
                $notif->assigned_user_id = $bean->assigned_user_id; // notyfikacja dla osoby przypisanej
                $notif->confirmation = 1;
            }
        }

        // zwykły urlop
        if(!$bean->sick_leave) {
            if(!$bean->withdrawal_of_leave_c){
                if($user_agreement == 1 ) { $notify_name = $full_name .' holiday request'; } // nazwa dla umowy o pracę
                else { $notify_name = $full_name .' absence request'; } // nazwa dla reszty umów
            } else { $notify_name = $full_name .' want to withdrawn '. $gender .' free day'; } // odebranie dni wolnych

            $notif->assigned_user_id = $reports_to; // dla przełożonego
            $description = $this->getUserTasks($bean->assigned_user_id, $bean->v_from, $bean->v_to); // przypisane zadania podczas urlopu
            $this->db->query('UPDATE ac_holiday SET description = "'. $description .'" WHERE id = "'. $bean->id .'"');

            // team leader lub administracja z wyłączeniem Madzi
            if(($current_user_class == 1 || $current_user_class == 2) && ($current_user->id != self::ME)) {
                $this->updateAssents(true, false, false, $bean); // potwierdzenie przez team leadera
            }
            // Madzia tworzy wniosek
            if($current_user->id == self::ME) {

                // wniosek o Madzi urlopie leci do Jakuba
                if($current_user->id == $bean->assigned_user_id) {
                    $notif->assigned_user_id = self::JJ;
                    $this->updateAssents(true, true, false, $bean); // potwierdzone przez administrację i team leadera
                } else { // Madzia wystawia wniosek dla kogoś
                    $row = $this->getUserInformations($bean->assigned_user_id, true);
                    $reports_to = $row['reports_to_id'];

                    // departament z wykluczeniem administracji
                    if($row['ile'] != 0)  {
                        $notif->assigned_user_id = $reports_to;

                        if($assigned_user_class == 0) { $this->updateAssents(false, true, false, $bean); } // potwierdzenie administracji
                        elseif($assigned_user_class == 1) { $this->updateAssents(true, true, false, $bean); } // potwierdzenie team leadera i administracji
                        else {
                            $this->updateAssents(true, true, true, $bean); // potwierdzenie wszystkich świętych
                            $this->setBoundary($bean, $notif, $user_agreement);
                            $set_name = false; // wniosek zaakceptowany, nazwa brana z setBoundary
                            $notif->confirmation = 1;
                        }
                    // administracja
                    } else {
                        $notif->assigned_user_id = self::JJ; // notyfikacja dla Jakuba, jeżeli DEV to dla Artura
                        $this->updateAssents(true, true, false, $bean); // potwierdzenie team leadera i administracji
                    }
                }
            }
            // zarząd wystawia wniosek urlopowy
            if($current_user_class == 3) {
                $this->updateAssents(true, false, true, $bean); // potwierdzenie administracji i zarządu
                $notif->assigned_user_id = self::ME;
            }
        }

        // $GLOBALS['log']->error('--------------------------------------------------------------------------------------------------------------------------------');
        // $GLOBALS['log']->error('user class: ' . $current_user_class. ', current user id: ' . $current_user->id . ', assigned user id: ' . $bean->assigned_user_id);
        // $GLOBALS['log']->error('sick leave: ' . $bean->sick_leave .', withdrawal of leave: '. $bean->withdrawal_of_leave_c);
        // $GLOBALS['log']->error('supervisor: ' . $bean->supervisor . ', fa: ' . $bean->fa_c .', board: ' . $bean->board .', aggrement: '. $user_agreement);

        if($set_name == true) { $notif->name = $notify_name; }
        $notif->created_by = $current_user->id;
        $notif->severity = "holidays";
        $notif->parent_type = 'AC_Holiday';
        $notif->parent_id = $bean->id;
        $notif->is_read = 0;
        $notif->save();
        $notif = null;
        unset($notif);
    }

    function editNotification(&$bean, $event, $arguments, $current_user, $notif)
    {
        $user_agreement = 0; // umowa zlecenie
        $sql_agreement = $this->db->query('SELECT aggrement_c FROM users_cstm WHERE id_c="'. $bean->assigned_user_id.'" AND aggrement_c LIKE "umowa_o_prace%"');
        if($this->db->getRowCount($sql_agreement) > 0 ) { $user_agreement = 1; } // umowa o pracę

        // odrzucony wniosek
        if($bean->rejected_c) {
            $this->db->query('UPDATE ac_holiday_cstm SET fa_c = 1 WHERE id_c = "'. $bean->id .'"'); // zatwierdzone przez fa

            // notyfikacja dla użytkownika
            $notif->name = $this->setResponse(1, $user_agreement, $current_user->full_name);
            $notif->assigned_user_id = $bean->assigned_user_id;
            $notif->confirmation = 1;
        } else {
            $dev_team = $this->getDevTeam($bean->assigned_user_id);
            $user_class = $this->getUserClass($current_user->id);

            // akceptacja:
            // klasa przeglądającego użytkownika:
            if($bean->supervisor && !$bean->board && ($user_class == 1 || $user_class == 2)) { // team leadera, 1 lub 2
                $notif->assigned_user_id = ($dev_team == true) ? self::AW : self::JJ;
            } elseif($bean->supervisor && $bean->board && $user_class == 2) { // team leadera i zarządu, 2
                $this->setBoundary($bean, $notif, $user_agreement);
                $notif->confirmation = 1;
            } elseif($bean->supervisor && $bean->board && !$bean->fa_c && $user_class == 3) { // team leadera i zarządu, bez fa, 3
                $notif->assigned_user_id = self::ME;
                $notif->deleted = 0;
            } elseif($bean->supervisor && $bean->board && $bean->fa_c && !($bean->sick_leave || $bean->withdrawal_of_leave_c) && $user_class == 3) { // team leadera, fa i zarządu i nie chorobowy lub odebranie, 3
                $this->setBoundary($bean, $notif, $user_agreement);
                $notif->confirmation = 1;
            } elseif($bean->supervisor && $bean->board && $bean->fa_c && ($bean->sick_leave || $bean->withdrawal_of_leave_c) && $user_class == 3) { // team leadera, fa i zarządu i chorobowy lub odebranie, 3
                if($bean->withdrawal_of_leave_c){
                    $notif->name = "Your withdrawn days request was accepted";
                } else {
                    $notif->name = "Your sick leave was accepted";
                }
                $notif->confirmation = 1;
                $notif->assigned_user_id = $bean->assigned_user_id;
            } else { // notyfikacja wraca do użytkownika składającego wniosek
                if( $current_user->id == $bean->assigned_user_id){ $notif->is_read = 1; }
                $notif->save();
                $notif = null;
                unset($notif);
                exit;
            }
        }

        // $GLOBALS['log']->error('--------------------------------------------------------------------------------------------------------------------------------');
        // $GLOBALS['log']->error('sick leave: ' . $bean->sick_leave .', withdrawal of leave: '. $bean->withdrawal_of_leave_c);
        // $GLOBALS['log']->error('supervisor: ' . $bean->supervisor . ', fa: ' . $bean->fa_c .', board: ' . $bean->board .', aggrement: '. $user_agreement);

        $notif->severity = "holidays";
        $notif->is_read = 0;
        $notif->save();
        $notif = null;
        unset($notif);
    }

    function deleteNotification($bean, $event, $arguments)
    {
        $this->db->query("UPDATE `notifications` SET `deleted` = 1, `is_read` = 1 WHERE parent_id = '". $bean->id ."'");

        if($bean->supervisor == 1 
            && $bean->board == 1 
            && $bean->fa_c == 1 
            && $bean->sick_leave == 0
            && $bean->withdrawal_of_leave_c == 0
            && strtotime(date("Y-m-d")) < strtotime($bean->v_from)
        ) {
            $this->db->query("UPDATE `users_cstm` SET 
                `overdue_free_days_c`=`overdue_free_days_c`+".$bean->days_hd.", 
                `used_free_days_c`=`used_free_days_c`-".$bean->days_hd."
                WHERE `id_c`='{$bean->assigned_user_id}'");
        }
    }
}
?>
