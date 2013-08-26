<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Маппер для работы с БД
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_MapperUser extends Mapper {
    /**
     * Добавляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return int|bool
     */
    public function Add(ModuleUser_EntityUser $oUser) {

        $sql
            = "INSERT INTO ?_user
			(user_login,
			user_password,
			user_mail,
			user_date_register,
			user_ip_register,
			user_activate,
			user_activate_key
			)
			VALUES(?, ?, ?, ?, ?, ?, ?)
		";
        $nUserId = $this->oDb->query(
            $sql, $oUser->getLogin(), $oUser->getPassword(), $oUser->getMail(), $oUser->getDateRegister(),
            $oUser->getIpRegister(), $oUser->getActivate(), $oUser->getActivateKey()
        );
        return $nUserId ? $nUserId : false;
    }

    /**
     * Обновляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return bool
     */
    public function Update(ModuleUser_EntityUser $oUser) {

        $sql
            = "
            UPDATE ?_user
            SET
                user_password = ?,
                user_mail = ?,
                user_skill = ?,
                user_date_activate = ?,
                user_date_comment_last = ?,
                user_rating = ?,
                user_count_vote = ?,
                user_activate = ?,
                user_activate_key = ?,
                user_profile_name = ?,
                user_profile_sex = ?,
                user_profile_country = ?,
                user_profile_region = ?,
                user_profile_city = ?,
                user_profile_birthday = ?,
                user_profile_about = ?,
                user_profile_date = ?,
                user_profile_avatar = ?,
                user_profile_foto = ?,
                user_settings_notice_new_topic = ?,
                user_settings_notice_new_comment = ?,
                user_settings_notice_new_talk = ?,
                user_settings_notice_reply_comment = ?,
                user_settings_notice_new_friend = ?,
                user_settings_timezone = ?,
                user_last_session = ?
            WHERE user_id = ?
        ";
        $bResult = $this->oDb->query(
            $sql,
            $oUser->getPassword(),
            $oUser->getMail(),
            $oUser->getSkill(),
            $oUser->getDateActivate(),
            $oUser->getDateCommentLast(),
            $oUser->getRating(),
            $oUser->getCountVote(),
            $oUser->getActivate(),
            $oUser->getActivateKey(),
            $oUser->getProfileName(),
            $oUser->getProfileSex(),
            $oUser->getProfileCountry(),
            $oUser->getProfileRegion(),
            $oUser->getProfileCity(),
            $oUser->getProfileBirthday(),
            $oUser->getProfileAbout(),
            $oUser->getProfileDate(),
            $oUser->getProfileAvatar(),
            $oUser->getProfileFoto(),
            $oUser->getSettingsNoticeNewTopic(),
            $oUser->getSettingsNoticeNewComment(),
            $oUser->getSettingsNoticeNewTalk(),
            $oUser->getSettingsNoticeReplyComment(),
            $oUser->getSettingsNoticeNewFriend(),
            $oUser->getSettingsTimezone(),
            $oUser->getLastSession(),
            $oUser->getId()
        );
        return $bResult !== false;
    }

    /**
     * Получить юзера по ключу сессии
     *
     * @param string $sKey    Сессионный ключ
     *
     * @return int|null
     */
    public function GetUserBySessionKey($sKey) {

        $sql
            = "
            SELECT
				s.user_id
			FROM
				?_session AS s
			WHERE
				s.session_key = ?
			LIMIT 1
			";
        if ($nUserId = $this->oDb->selectCell($sql, $sKey)) {
            return intval($nUserId);
        }
        return null;
    }

    /**
     * Создание пользовательской сессии
     *
     * @param ModuleUser_EntitySession $oSession
     *
     * @return bool
     */
    public function CreateSession(ModuleUser_EntitySession $oSession) {

        $sql
            = "REPLACE INTO ?_session
			SET
				session_key = ? ,
				user_id = ?d ,
				session_ip_create = ? ,
				session_ip_last = ? ,
				session_date_create = ? ,
				session_date_last = ? ,
				session_agent_hash = ?
		";
        $bResult = $this->oDb->query(
            $sql,
            $oSession->getKey(),
            $oSession->getUserId(),
            $oSession->getIpCreate(),
            $oSession->getIpLast(),
            $oSession->getDateCreate(),
            $oSession->getDateLast(),
            $oSession->getUserAgentHash()
        );
        return ($bResult !== false);
    }

    public function LimitSession($oUser, $nSessionLimit) {

        // Число сессий не может быть меньше 1
        if ($nSessionLimit < 1) {
            return;
        }

        if (is_object($oUser)) {
            $nUserId = $oUser->GetId();
        } else {
            $nUserId = intval($oUser);
        }

        $sql
            = "
            SELECT
                session_date_last
            FROM ?_session
            WHERE user_id=?d
            ORDER BY session_date_last DESC
            LIMIT ?d
        ";
        $aRows = $this->oDb->selectCol($sql, $nUserId, $nSessionLimit + 1);
        if ($aRows && sizeof($aRows) > $nSessionLimit) {
            $sDate = end($aRows);
            $sql
                = "
                DELETE FROM ?_session
                WHERE user_id=?d AND session_date_last<=?
            ";
            $this->oDb->query($sql, $nUserId, $sDate);
        }
    }

    /**
     * Обновление данных сессии
     *
     * @param ModuleUser_EntitySession $oSession
     *
     * @return int|bool
     */
    public function UpdateSession(ModuleUser_EntitySession $oSession) {

        $sql
            = "UPDATE ?_session
			SET
				session_ip_last = ? ,
				session_date_last = ? ,
				session_exit = ?
			WHERE session_key = ?
		";
        $bResult = $this->oDb->query(
            $sql, $oSession->getIpLast(), $oSession->getDateLast(), $oSession->getDateExit(), $oSession->getKey()
        );
        return $bResult !== false;
    }

    /**
     * Closes all sessions of specifier user
     *
     * @param   object|int $oUser
     *
     * @return  bool
     */
    public function CloseUserSessions($oUser) {

        if (is_object($oUser)) {
            $nUserId = $oUser->GetId();
        } else {
            $nUserId = intval($oUser);
        }

        $sql
            = "
            UPDATE ?_session
            SET
                session_exit = ?
            WHERE user_id = ?
            ";
        return ($this->oDb->query($sql, F::Now(), $nUserId) !== false);
    }

    /**
     * Список сессий юзеров по ID
     *
     * @param   array $aArrayId    Список ID пользователей
     *
     * @return  array
     */
    public function GetSessionsByArrayId($aArrayId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql
            = "
            SELECT
				s.*
			FROM
			    ?_user as u
				INNER JOIN ?_session as s ON s.session_key=u.user_last_session
			WHERE
				u.user_id IN(?a)
			LIMIT " . count($aArrayId) . "
			";
        $aRes = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId)) {
            foreach ($aRows as $aRow) {
                $aRes[] = Engine::GetEntity('User_Session', $aRow);
            }
        }
        return $aRes;
    }

    /**
     * Список юзеров по ID
     *
     * @param array $aArrayId Список ID пользователей
     *
     * @return array
     */
    public function GetUsersByArrayId($aArrayId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql
            = "
            SELECT
				u.*,
				IF(ua.user_id IS NULL,0,1) as user_is_administrator,
				ab.banline, ab.banunlim, ab.banactive
			FROM
				?_user as u
				LEFT JOIN ?_user_administrator AS ua ON u.user_id=ua.user_id
				LEFT JOIN ?_adminban AS ab ON u.user_id=ab.user_id AND ab.banactive=1
			WHERE
				u.user_id IN(?a)
			ORDER BY FIELD(u.user_id,?a)
			LIMIT " . count($aArrayId) . "
			";
        $aUsers = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $aArrayId)) {
            foreach ($aRows as $aUser) {
                $aUsers[] = Engine::GetEntity('User', $aUser);
            }
        }
        return $aUsers;
    }

    /**
     * Получить юзера по ключу активации
     *
     * @param string $sKey    Ключ активации
     *
     * @return int|null
     */
    public function GetUserByActivateKey($sKey) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE u.user_activate_key = ?
			LIMIT 1
			";
        if ($aRow = $this->oDb->selectRow($sql, $sKey)) {
            return $aRow['user_id'];
        }
        return null;
    }

    /**
     * Получить юзера по мылу
     *
     * @param string $sMail    Емайл
     *
     * @return int|null
     */
    public function GetUserIdByMail($sMail) {

        return $this->GetUserByMail($sMail);
    }

    /**
     * Получить юзера по мылу
     *
     * @param string $sMail    Емайл
     *
     * @return int|null
     */
    public function GetUserByMail($sMail) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE u.user_mail = ?
			LIMIT 1
			";
        return intval($this->oDb->selectCell($sql, $sMail));
    }

    /**
     * Получить юзера по логину
     *
     * @param string $sLogin Логин пользователя
     *
     * @return int|null
     */
    public function GetUserByLogin($sLogin) {

        return $this->GetUserIdByLogin($sLogin);
    }

    /**
     * Получить ID юзера по логину
     *
     * @param string $sLogin Логин пользователя
     *
     * @return int
     */
    public function GetUserIdByLogin($sLogin) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE
				u.user_login = ?
			LIMIT 1
			";
        return intval($this->oDb->selectCell($sql, $sLogin));
    }

    /**
     * Получить список юзеров по дате последнего визита
     *
     * @param int $iLimit Количество
     *
     * @return array
     */
    public function GetUsersByDateLast($iLimit) {

        $sql
            = "SELECT
			user_id
			FROM
				?_session
			ORDER BY
				session_date_last DESC
			LIMIT 0, ?d
				";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['user_id'];
            }
        }
        return $aReturn;
    }

    /**
     * Получить список юзеров по дате регистрации
     *
     * @param int $iLimit    Количество
     *
     * @return array
     */
    public function GetUsersByDateRegister($iLimit) {

        $sql
            = "SELECT
			user_id
			FROM
				?_user
			WHERE
				 user_activate = 1
			ORDER BY
				user_id DESC
			LIMIT 0, ?d
				";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['user_id'];
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает общее количество пользователй
     *
     * @return int
     */
    public function GetCountUsers() {

        $sql = "SELECT count(*) as count FROM ?_user";
        return $this->oDb->selectCell($sql);
    }

    public function GetCountAdmins() {

        $sql = "SELECT count(*) as count FROM ?_user_administrator ";
        return $this->oDb->selectCell($sql);
    }

    /**
     * Возвращает количество активных пользователей
     *
     * @param string $sDateActive    Дата
     *
     * @return mixed
     */
    public function GetCountUsersActive($sDateActive) {

        $sql = "SELECT user_id FROM ?_session WHERE session_date_last >= ? GROUP BY user_id";
        $aRows = $this->oDb->select($sql, $sDateActive);
        return $aRows ? count($aRows) : 0;
    }

    /**
     * Возвращает количество пользователей в разрезе полов
     *
     * @return array
     */
    public function GetCountUsersSex() {

        $sql
            = "SELECT user_profile_sex  AS ARRAY_KEY, count(*) as count FROM ?_user WHERE user_activate = 1 GROUP BY user_profile_sex ";
        $result = $this->oDb->select($sql);
        return $result;
    }

    /**
     * Получить список юзеров по первым  буквам логина
     *
     * @param string $sUserLogin    Логин
     * @param int    $iLimit        Количество
     *
     * @return array
     */
    public function GetUsersByLoginLike($sUserLogin, $iLimit) {

        $sql
            = "SELECT
				user_id
			FROM
				?_user
			WHERE
				user_activate = 1
				and
				user_login LIKE ?
			LIMIT 0, ?d
				";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $sUserLogin . '%', $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['user_id'];
            }
        }
        return $aReturn;
    }

    /**
     * Добавляет друга
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function AddFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "INSERT INTO ?_friend
			(user_from,
			user_to,
			status_from,
			status_to
			)
			VALUES(?d, ?d, ?d, ?d)
		";
        if (
            $this->oDb->query(
                $sql,
                $oFriend->getUserFrom(),
                $oFriend->getUserTo(),
                $oFriend->getStatusFrom(),
                $oFriend->getStatusTo()
            ) === 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Удаляет информацию о дружбе из базы данных
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function EraseFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "DELETE FROM ?_friend
			WHERE
				user_from = ?d
				AND
				user_to = ?d
		";
        if ($this->oDb->query($sql, $oFriend->getUserFrom(), $oFriend->getUserTo())) {
            return true;
        }
        return false;
    }

    /**
     * Обновляет информацию о друге
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function UpdateFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "
			UPDATE ?_friend
			SET
				status_from = ?d,
				status_to   = ?d
			WHERE
				user_from = ?d
				AND
				user_to = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFriend->getStatusFrom(),
            $oFriend->getStatusTo(),
            $oFriend->getUserFrom(),
            $oFriend->getUserTo()
        );
        return $bResult !== false;
    }

    /**
     * Получить список отношений друзей
     *
     * @param  array $aArrayId    Список ID пользователей проверяемых на дружбу
     * @param  int   $nUserId     ID пользователя у которого проверяем друзей
     *
     * @return array
     */
    public function GetFriendsByArrayId($aArrayId, $nUserId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					*
				FROM
					?_friend
				WHERE
					( `user_from`=?d AND `user_to` IN(?a) )
					OR
					( `user_from` IN(?a) AND `user_to`=?d )
				";
        $aRows = $this->oDb->select(
            $sql,
            $nUserId, $aArrayId,
            $aArrayId, $nUserId
        );
        $aRes = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aRow['user'] = $nUserId;
                $aRes[] = Engine::GetEntity('User_Friend', $aRow);
            }
        }
        return $aRes;
    }

    /**
     * Получает список друзей
     *
     * @param  int $nUserId      ID пользователя
     * @param  int $iCount       Возвращает общее количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetUsersFriend($nUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "SELECT
					uf.user_from,
					uf.user_to
				FROM
					?_friend as uf
				WHERE
					( uf.user_from = ?d
					OR
					uf.user_to = ?d )
					AND
					( 	uf.status_from + uf.status_to = ?d
					OR
						(uf.status_from = ?d AND uf.status_to = ?d )
					)
				LIMIT ?d, ?d ;";
        $aUsers = array();
        $aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            $nUserId,
            $nUserId,
            ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER,
            ModuleUser::USER_FRIEND_ACCEPT,
            ModuleUser::USER_FRIEND_ACCEPT,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = ($aUser['user_from'] == $nUserId)
                    ? $aUser['user_to']
                    : $aUser['user_from'];
            }
        }
        rsort($aUsers, SORT_NUMERIC);
        return array_unique($aUsers);
    }

    /**
     * Получает количество друзей
     *
     * @param  int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUsersFriend($nUserId) {

        $sql
            = "SELECT
					count(*) as c
				FROM
					?_friend as uf
				WHERE
					( uf.user_from = ?d
					OR
					uf.user_to = ?d )
					AND
					( 	uf.status_from + uf.status_to = ?d
					OR
						(uf.status_from = ?d AND uf.status_to = ?d )
					)";
        $aRow = $this->oDb->selectRow(
            $sql,
            $nUserId,
            $nUserId,
            ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER,
            ModuleUser::USER_FRIEND_ACCEPT,
            ModuleUser::USER_FRIEND_ACCEPT
        );
        if ($aRow) {
            return $aRow['c'];
        }
        return 0;
    }

    /**
     * Получить список заявок на добавление в друзья от указанного пользователя
     *
     * @param  string $nUserId
     * @param  int    $nStatus Статус запроса со стороны добавляемого
     *
     * @return array
     */
    public function GetUsersFriendOffer($nUserId, $nStatus = ModuleUser::USER_FRIEND_NULL) {

        $sql
            = "SELECT
					uf.user_to
				FROM
					?_friend as uf
				WHERE
					uf.user_from = ?d
					AND
					uf.status_from = ?d
					AND
					uf.status_to = ?d
				;";
        $aUsers = array();
        $aRows = $this->oDb->select(
            $sql,
            $nUserId,
            ModuleUser::USER_FRIEND_OFFER,
            $nStatus
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = $aUser['user_to'];
            }
        }
        return $aUsers;
    }

    /**
     * Получить список заявок на добавление в друзья от указанного пользователя
     *
     * @param  string $nUserId
     * @param  int    $nStatus Статус запроса со стороны самого пользователя
     *
     * @return array
     */
    public function GetUserSelfFriendOffer($nUserId, $nStatus = ModuleUser::USER_FRIEND_NULL) {

        $sql
            = "SELECT
					uf.user_from
				FROM
					?_friend as uf
				WHERE
					uf.user_to = ?d
					AND
					uf.status_from = ?d
					AND
					uf.status_to = ?d
				;";
        $aUsers = array();
        $aRows = $this->oDb->select(
            $sql,
            $nUserId,
            ModuleUser::USER_FRIEND_OFFER,
            $nStatus
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = $aUser['user_from'];
            }
        }
        return $aUsers;
    }

    /**
     * Получает инвайт по его коду
     *
     * @param  string $sCode    Код инвайта
     * @param  int    $iUsed    Флаг испольщования инвайта
     *
     * @return ModuleUser_EntityInvite|null
     */
    public function GetInviteByCode($sCode, $iUsed = 0) {

        $sql = "SELECT * FROM ?_invite WHERE invite_code = ? AND invite_used = ?d ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode, $iUsed)) {
            return Engine::GetEntity('User_Invite', $aRow);
        }
        return null;
    }

    /**
     * Добавляет новый инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    Объект инвайта
     *
     * @return int|bool
     */
    public function AddInvite(ModuleUser_EntityInvite $oInvite) {

        $sql
            = "INSERT INTO ?_invite
			(invite_code,
			user_from_id,
			invite_date_add
			)
			VALUES(?,  ?,	?)
		";
        $nId = $this->oDb->query($sql, $oInvite->getCode(), $oInvite->getUserFromId(), $oInvite->getDateAdd());
        return $nId ? $nId : false;
    }

    /**
     * Обновляет инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    бъект инвайта
     *
     * @return bool
     */
    public function UpdateInvite(ModuleUser_EntityInvite $oInvite) {

        $sql
            = "UPDATE ?_invite
			SET
				user_to_id = ? ,
				invite_date_used = ? ,
				invite_used =?
			WHERE invite_id = ?
		";
        $bResult = $this->oDb->query(
            $sql, $oInvite->getUserToId(), $oInvite->getDateUsed(), $oInvite->getUsed(), $oInvite->getId()
        );
        return $bResult !== false;
    }

    /**
     * Получает число использованых приглашений юзером за определенную дату
     *
     * @param int    $nUserIdFrom    ID пользователя
     * @param string $sDate          Дата
     *
     * @return int
     */
    public function GetCountInviteUsedByDate($nUserIdFrom, $sDate) {

        $sql = "SELECT COUNT(invite_id) AS count FROM ?_invite WHERE user_from_id = ?d AND invite_date_add >= ? ";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdFrom, $sDate)) {
            return $aRow['count'];
        }
        return 0;
    }

    /**
     * Получает полное число использованных приглашений юзера
     *
     * @param int $nUserIdFrom    ID пользователя
     *
     * @return int
     */
    public function GetCountInviteUsed($nUserIdFrom) {

        $sql = "SELECT COUNT(invite_id) AS count FROM ?_invite WHERE user_from_id = ?d";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdFrom)) {
            return $aRow['count'];
        }
        return 0;
    }

    /**
     * Получает список приглашенных юзеров
     *
     * @param int $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetUsersInvite($nUserId) {

        $sql
            = "
            SELECT
				i.user_to_id
			FROM
				?_invite as i
			WHERE
				i.user_from_id = ?d
			";
        $aUsers = $this->oDb->selectCol($sql, $nUserId);
        return (array)$aUsers;
    }

    /**
     * Получает юзера который пригласил
     *
     * @param int $nUserIdTo    ID пользователя
     *
     * @return int|null
     */
    public function GetUserInviteFrom($nUserIdTo) {

        $sql
            = "SELECT
					i.user_from_id
				FROM
					?_invite as i
				WHERE
					i.user_to_id = ?d
				LIMIT 0,1;
					";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdTo)) {
            return $aRow['user_from_id'];
        }
        return null;
    }

    /**
     * Добавляем воспоминание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function AddReminder(ModuleUser_EntityReminder $oReminder) {

        $sql
            = "REPLACE ?_reminder
			SET
				reminder_code = ? ,
				user_id = ? ,
				reminder_date_add = ? ,
				reminder_date_used = ? ,
				reminder_date_expire = ? ,
				reminde_is_used = ?
		";
        return $this->oDb->query(
            $sql, $oReminder->getCode(), $oReminder->getUserId(), $oReminder->getDateAdd(), $oReminder->getDateUsed(),
            $oReminder->getDateExpire(), $oReminder->getIsUsed()
        );
    }

    /**
     * Сохраняем воспомнинание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function UpdateReminder(ModuleUser_EntityReminder $oReminder) {

        return $this->AddReminder($oReminder);
    }

    /**
     * Получаем запись восстановления пароля по коду
     *
     * @param string $sCode    Код восстановления пароля
     *
     * @return ModuleUser_EntityReminder|null
     */
    public function GetReminderByCode($sCode) {

        $sql
            = "SELECT
					*
				FROM
					?_reminder
				WHERE
					reminder_code = ?
				LIMIT 1
				";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return Engine::GetEntity('User_Reminder', $aRow);
        }
        return null;
    }

    /**
     * Получить дополнительные поля профиля пользователя
     *
     * @param array|null $aType Типы полей, null - все типы
     *
     * @return array
     */
    public function getUserFields($aType) {

        if (!is_null($aType) && !is_array($aType)) {
            $aType = array($aType);
        }
        $sql = 'SELECT * FROM ?_user_field WHERE 1=1 { AND type IN (?a) }';
        $aFields = $this->oDb->select($sql, (is_null($aType) || !count($aType)) ? DBSIMPLE_SKIP : $aType);
        if (!count($aFields)) {
            return array();
        }
        $aResult = array();
        foreach ($aFields as $aField) {
            $aResult[$aField['id']] = Engine::GetEntity('User_Field', $aField);
        }
        return $aResult;
    }

    /**
     * Получить по имени поля его значение для определённого пользователя
     *
     * @param int    $nUserId    ID пользователя
     * @param string $sName      Имя поля
     *
     * @return string
     */
    public function getUserFieldValueByName($nUserId, $sName) {

        $sql
            = "
            SELECT value
            FROM ?_user_field_value
            WHERE
                user_id = ?d
                AND
                field_id = (SELECT id FROM ?_user_field WHERE name =?)";
        $ret = $this->oDb->selectCol($sql, $nUserId, $sName);
        return $ret[0];
    }

    /**
     * Получить значения дополнительных полей профиля пользователя
     *
     * @param int   $nUserId      ID пользователя
     * @param bool  $bOnlyNoEmpty Загружать только непустые поля
     * @param array $aType        Типы полей, null - все типы
     *
     * @return array
     */
    public function getUserFieldsValues($nUserId, $bOnlyNoEmpty, $aType) {

        if (!is_null($aType) && !is_array($aType)) {
            $aType = array($aType);
        }
        /*
         * Если запрашиваем без типа, то необходимо вернуть ВСЕ возможные поля с этим типом,
         * в не звависимости, указал ли их пользователь у себя в профили или нет
         * Выглядит костыльно
         */
        if (is_array($aType) && count($aType) == 1 && $aType[0] == '') {
            $sql
                = "
                SELECT f.*, v.value FROM ?_user_field AS f
                    LEFT JOIN ?_user_field_value AS v ON f.id = v.field_id
                WHERE v.user_id = ?d AND f.type IN (?a)";

        } else {
            $sql
                = "
                SELECT v.value, f.*
                FROM ?_user_field_value AS v, ?_user_field AS f
                WHERE
                    v.user_id = ?d
                    AND v.field_id = f.id
                    { AND f.type IN (?a) }";
        }
        $aResult = array();
        $aRows = $this->oDb->select($sql, $nUserId, (is_null($aType) || !count($aType)) ? DBSIMPLE_SKIP : $aType);
        if ($aRows) {
            foreach ($aRows as $aRow) {
                if ($bOnlyNoEmpty && !$aRow['value']) {
                    continue;
                }
                $aResult[] = Engine::GetEntity('User_Field', $aRow);
            }
        }
        return $aResult;
    }

    /**
     * Установить значения дополнительных полей профиля пользователя
     *
     * @param int   $nUserId    ID пользователя
     * @param array $aFields    Ассоциативный массив полей id => value
     * @param int   $iCountMax  Максимальное количество одинаковых полей
     *
     * @return bool
     */
    public function setUserFieldsValues($nUserId, $aFields, $iCountMax) {

        if (!count($aFields)) {
            return;
        }
        foreach ($aFields as $iId => $sValue) {
            $sql = "SELECT count(*) as c FROM ?_user_field_value WHERE user_id = ?d AND field_id = ?";
            $aRow = $this->oDb->selectRow($sql, $nUserId, $iId);
            $iCount = isset($aRow['c']) ? $aRow['c'] : 0;
            if ($iCount < $iCountMax) {
                $sql = "INSERT INTO ?_user_field_value SET value = ?, user_id = ?d, field_id = ?";
            } elseif ($iCount == $iCountMax && $iCount == 1) {
                $sql = "UPDATE ?_user_field_value SET value = ? WHERE user_id = ?d AND field_id = ?";
            } else {
                continue;
            }
            $this->oDb->query($sql, $sValue, $nUserId, $iId);
        }
    }

    /**
     * Добавить поле
     *
     * @param ModuleUser_EntityField $oField    Объект пользовательского поля
     *
     * @return bool
     */
    public function addUserField($oField) {

        $sql
            = "
            INSERT INTO ?_user_field
            SET
                name = ?,
                title = ?,
                pattern = ?,
                type = ?";
        $xResult = $this->oDb->query(
            $sql, $oField->getName(), $oField->getTitle(), $oField->getPattern(), $oField->getType()
        );
        return $xResult !== false;
    }

    /**
     * Удалить поле
     *
     * @param int $iId    ID пользовательского поля
     *
     * @return bool
     */
    public function deleteUserField($iId) {

        $sql = 'DELETE FROM ?_user_field_value WHERE field_id = ?d';
        $this->oDb->query($sql, $iId);
        $sql
            = 'DELETE FROM ?_user_field WHERE
                    id = ?d';
        $this->oDb->query($sql, $iId);
        return true;
    }

    /**
     * Изменить поле
     *
     * @param ModuleUser_EntityField $oField    Объект пользовательского поля
     *
     * @return bool
     */
    public function updateUserField($oField) {

        $sql
            = '
            UPDATE ?_user_field
            SET
                name = ?,
                title = ?,
                pattern = ?,
                type = ?
            WHERE id = ?d';
        $xResult = $this->oDb->query(
            $sql,
            $oField->getName(),
            $oField->getTitle(),
            $oField->getPattern(),
            $oField->getType(),
            $oField->getId()
        );
        return $xResult;
    }

    /**
     * Проверяет существует ли поле с таким именем
     *
     * @param string   $sName  Имя поля
     * @param int|null $nId    ID поля
     *
     * @return bool
     */
    public function userFieldExistsByName($sName, $nId) {

        $sql = 'SELECT id FROM  ?_user_field WHERE name = ? {AND id != ?d}';
        return $this->oDb->select($sql, $sName, $nId ? $nId : DBSIMPLE_SKIP);
    }

    /**
     * Проверяет существует ли поле с таким ID
     *
     * @param int $nId    ID поля
     *
     * @return bool
     */
    public function userFieldExistsById($nId) {

        $sql = "SELECT id FROM  ?_user_field WHERE id = ?d";
        return $this->oDb->select($sql, $nId);
    }

    /**
     * Удаляет у пользователя значения полей
     *
     * @param   int|array  $aUsersId   ID пользователя или массив ID
     * @param   array|null $aTypes     Список типов для удаления
     *
     * @return  bool
     */
    public function DeleteUserFieldValues($aUsersId, $aTypes = null) {

        $aUsersId = $this->_arrayId($aUsersId);
        if (!$aTypes) {
            $sql
                = "
                DELETE FROM ?_user_field_value
                WHERE user_id IN (?a)
            ";
            return $this->oDb->query($sql, $aUsersId) !== false;
        } else {
            if (!is_array($aTypes)) {
                $aTypes = array($aTypes);
            }
            $sql
                = "
                DELETE FROM ?_user_field_value
                WHERE user_id IN (?a) AND field_id IN
                    (SELECT id FROM ?_user_field WHERE type IN (?a))
            ";
            return $this->oDb->query($sql, $aUsersId, $aTypes);
        }
    }

    /**
     * Возвращает список заметок пользователя
     *
     * @param int $iUserId      ID пользователя
     * @param int $iCount       Возвращает общее количество элементов
     * @param int $iCurrPage    Номер страницы
     * @param int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetUserNotesByUserId($iUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "
			SELECT *
			FROM
				?_user_note
			WHERE
				user_id = ?d
			ORDER BY id DESC
			LIMIT ?d, ?d ";
        $aReturn = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, $iUserId, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = Engine::GetEntity('ModuleUser_EntityNote', $aRow);
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает количество заметок у пользователя
     *
     * @param int $iUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUserNotesByUserId($iUserId) {

        $sql
            = "
			SELECT COUNT(*) as c
			FROM
				?_user_note
			WHERE
				user_id = ?d
			";
        $nCnt = $this->oDb->selectCell($sql, $iUserId);
        return $nCnt ? $nCnt : 0;
    }

    /**
     * Возвращет заметку по автору и пользователю
     *
     * @param int $iTargetUserId    ID пользователя о ком заметка
     * @param int $iUserId          ID пользователя автора заметки
     *
     * @return ModuleUser_EntityNote|null
     */
    public function GetUserNote($iTargetUserId, $iUserId) {

        $sql = "SELECT * FROM ?_user_note WHERE target_user_id = ?d AND user_id = ?d ";
        if ($aRow = $this->oDb->selectRow($sql, $iTargetUserId, $iUserId)) {
            return Engine::GetEntity('ModuleUser_EntityNote', $aRow);
        }
        return null;
    }

    /**
     * Возвращает заметку по ID
     *
     * @param int $iId    ID заметки
     *
     * @return ModuleUser_EntityNote|null
     */
    public function GetUserNoteById($iId) {

        $sql = "
            SELECT *
            FROM ?_user_note
            WHERE id = ?d
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $iId)) {
            return Engine::GetEntity('ModuleUser_EntityNote', $aRow);
        }
        return null;
    }

    /**
     * Возвращает список заметок пользователя по ID целевых юзеров
     *
     * @param array $aArrayId    Список ID целевых пользователей
     * @param int   $nUserId     ID пользователя, кто оставлял заметки
     *
     * @return array
     */
    public function GetUserNotesByArrayUserId($aArrayId, $nUserId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }
        $sql
            = "SELECT
					*
				FROM
					?_user_note
				WHERE target_user_id IN (?a) AND user_id = ?d
				";
        $aRows = $this->oDb->select($sql, $aArrayId, $nUserId);
        $aRes = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aRes[] = Engine::GetEntity('ModuleUser_EntityNote', $aRow);
            }
        }
        return $aRes;
    }

    /**
     * Удаляет заметку по ID
     *
     * @param int $iId    ID заметки
     *
     * @return bool
     */
    public function DeleteUserNoteById($iId) {

        $sql = "DELETE FROM ?_user_note WHERE id = ?d ";
        return $this->oDb->query($sql, $iId);
    }

    /**
     * Добавляет заметку
     *
     * @param ModuleUser_EntityNote $oNote    Объект заметки
     *
     * @return int|null
     */
    public function AddUserNote($oNote) {

        $sql = "INSERT INTO ?_user_note SET ?a ";
        if ($iId = $this->oDb->query($sql, $oNote->_getData())) {
            return $iId;
        }
        return false;
    }

    /**
     * Обновляет заметку
     *
     * @param ModuleUser_EntityNote $oNote    Объект заметки
     *
     * @return int
     */
    public function UpdateUserNote($oNote) {

        $sql
            = "UPDATE ?_user_note
			SET
			 	text = ?
			WHERE id = ?d
		";
        $bResult = $this->oDb->query($sql, $oNote->getText(), $oNote->getId());
        return $bResult !== false;
    }

    /**
     * Добавляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return int|null
     */
    public function AddUserChangemail($oChangemail) {

        $sql = "INSERT INTO ?_user_changemail SET ?a ";
        if ($iId = $this->oDb->query($sql, $oChangemail->_getData())) {
            return $iId;
        }
        return false;
    }

    /**
     * Обновляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return int
     */
    public function UpdateUserChangemail($oChangemail) {

        $sql
            = "UPDATE ?_user_changemail
			SET
			 	date_used = ?,
			 	confirm_from = ?d,
			 	confirm_to = ?d
			WHERE id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oChangemail->getDateUsed(), $oChangemail->getConfirmFrom(), $oChangemail->getConfirmTo(),
            $oChangemail->getId()
        );
        return $bResult !== false;
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeFrom($sCode) {

        $sql = "
            SELECT *
            FROM ?_user_changemail
            WHERE code_from = ?
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return Engine::GetEntity('ModuleUser_EntityChangemail', $aRow);
        }
        return null;
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeTo($sCode) {

        $sql = "
            SELECT *
            FROM ?_user_changemail
            WHERE code_to = ?
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return Engine::GetEntity('ModuleUser_EntityChangemail', $aRow);
        }
        return null;
    }

    /**
     * Возвращает список пользователей по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает общее количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элментов на страницу
     *
     * @return array
     */
    public function GetUsersByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        if (isset($aFilter['login']) && $aFilter['login'] && is_string($aFilter['login'])) {
            if (strpos($aFilter['login'], '%') === false) {
                $aFilter['login'] .= '%';
            }
        }
        if (isset($aFilter['regdate']) && $aFilter['regdate']) {
            if (strpos($aFilter['regdate'], '%') === false) {
                $aFilter['regdate'] .= '%';
            }
        }
        if (isset($aFilter['ip']) && $aFilter['ip']) {
            $aFilter['ip_register'] = F::IpRange($aFilter['ip']);
        }
        $aOrderAllow = array('user_id', 'user_login', 'user_date_register', 'user_rating', 'user_skill',
                             'user_profile_name');
        $sOrder = '';
        if (is_array($aOrder) && $aOrder) {
            foreach ($aOrder as $key => $value) {
                if (!in_array($key, $aOrderAllow)) {
                    unset($aOrder[$key]);
                } elseif (in_array($value, array('asc', 'desc'))) {
                    $sOrder .= " {$key} {$value},";
                }
            }
            $sOrder = trim($sOrder, ',');
        }
        if ($sOrder == '') {
            $sOrder = ' user_id desc ';
        }
        $sOrder = str_replace(' user_id ', ' u.user_id ', $sOrder);

        $sql = "SELECT
					u.user_id
				FROM
					?_user AS u
				    LEFT JOIN ?_user_administrator AS a ON a.user_id=u.user_id
				WHERE
					1 = 1
					{ AND u.user_id = ?d }
					{ AND user_mail = ? }
					{ AND user_password = ? }
					{ AND (INET_ATON(user_ip_register) BETWEEN INET_ATON(?) AND  INET_ATON(?))}
					{ AND user_activate = ?d }
					{ AND user_activate_key = ? }
					{ AND user_profile_sex = ? }
					{ AND user_login LIKE ? }
					{ AND user_login IN (?a) }
					{ AND user_date_register LIKE ? }
					{ AND user_profile_name LIKE ? }
					{ AND NOT a.user_id IS NULL AND ?d > -1}
					{ AND a.user_id IS NULL AND ?d > -1}
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['email']) ? $aFilter['email'] : DBSIMPLE_SKIP,
            isset($aFilter['password']) ? $aFilter['password'] : DBSIMPLE_SKIP,
            isset($aFilter['ip_register']) ? $aFilter['ip_register'][0] : DBSIMPLE_SKIP,
            isset($aFilter['ip_register']) ? $aFilter['ip_register'][1] : DBSIMPLE_SKIP,
            isset($aFilter['activate']) ? $aFilter['activate'] : DBSIMPLE_SKIP,
            isset($aFilter['activate_key']) ? $aFilter['activate_key'] : DBSIMPLE_SKIP,
            isset($aFilter['profile_sex']) ? $aFilter['profile_sex'] : DBSIMPLE_SKIP,
            (isset($aFilter['login']) && is_string($aFilter['login'])) ? $aFilter['login'] : DBSIMPLE_SKIP,
            (isset($aFilter['login']) && is_array($aFilter['login'])) ? $aFilter['login'] : DBSIMPLE_SKIP,
            (isset($aFilter['regdate']) && $aFilter['regdate']) ? $aFilter['regdate'] : DBSIMPLE_SKIP,
            isset($aFilter['profile_name']) ? $aFilter['profile_name'] : DBSIMPLE_SKIP,
            (isset($aFilter['admin']) && $aFilter['admin']) ? $aFilter['admin'] : DBSIMPLE_SKIP,
            (isset($aFilter['admin']) && !$aFilter['admin']) ? $aFilter['admin'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['user_id'];
            }
        }
        return $aResult;
    }

    /**
     * Возвращает список префиксов логинов пользователей (для алфавитного указателя)
     *
     * @param int $iPrefixLength    Длина префикса
     *
     * @return array
     */
    public function GetGroupPrefixUser($iPrefixLength = 1) {

        $sql
            = "
			SELECT SUBSTRING(`user_login` FROM 1 FOR ?d ) as prefix
			FROM
				?_user
			WHERE
				user_activate = 1
			GROUP BY prefix
			ORDER BY prefix ";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $iPrefixLength)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = mb_strtoupper($aRow['prefix'], 'utf-8');
            }
        }
        return $aReturn;
    }

}

// EOF