SELECT Subquery.cal_id,
       Subquery.Member,
       Subquery.spc_users_id,
       Subquery.name,
       Subquery.description,
       Subquery.color,
       Subquery.admin_id,
       IFNULL(pl_calendar_user_share.permission,'see') AS permission
  FROM    test.pl_calendar_user_share pl_calendar_user_share
       RIGHT JOIN
          (SELECT Subquery.cal_id,
                  Subquery.Member,
                  Subquery.spc_users_id,
                  spc_calendar_calendars.name,
                  spc_calendar_calendars.description,
                  spc_calendar_calendars.color,
                  spc_calendar_calendars.admin_id
             FROM    test.spc_calendar_calendars spc_calendar_calendars
                  INNER JOIN
                     (SELECT DISTINCT
                             pl_calendar_user_share.cal_id,
                             spc_users.username AS Member,
                             spc_users.id AS spc_users_id
                        FROM    test.pl_calendar_user_share pl_calendar_user_share
                             INNER JOIN
                                test.spc_users spc_users
                             ON (pl_calendar_user_share.spc_user_id =
                                    spc_users.id)
                      UNION
                      SELECT DISTINCT
                             pl_calendar_team_share.cal_id,
                             teammember.Member,
                             spc_users.id
                        FROM    (   test.teammember teammember
                                 INNER JOIN
                                    test.spc_users spc_users
                                 ON (teammember.Member = spc_users.username))
                             INNER JOIN
                                test.pl_calendar_team_share pl_calendar_team_share
                             ON (pl_calendar_team_share.team =
                                    teammember.Team)
                       WHERE (teammember.deleted = 0)
                      UNION
                      SELECT DISTINCT
                             pl_calendar_position_share.cal_id,
                             positionmember.Member,
                             spc_users.id AS spc_users_id
                        FROM    (   test.positionmember positionmember
                                 INNER JOIN
                                    test.spc_users spc_users
                                 ON (positionmember.Member =
                                        spc_users.username))
                             INNER JOIN
                                test.pl_calendar_position_share pl_calendar_position_share
                             ON (pl_calendar_position_share.position =
                                    positionmember.Position)
                       WHERE (positionmember.deleted = 0)) Subquery
                  ON (spc_calendar_calendars.id = Subquery.cal_id)
           ORDER BY Subquery.cal_id ASC, Subquery.Member ASC) Subquery
       ON     (pl_calendar_user_share.cal_id = Subquery.cal_id)
          AND (pl_calendar_user_share.spc_user_id = Subquery.spc_users_id)
    ORDER BY Subquery.cal_id ASC, Subquery.Member ASC