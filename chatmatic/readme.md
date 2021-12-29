Requirements:

- PHP Extensions: pgsql pdo_pgsql zip pdo_mysql (pdo_mysql required due to tntsearch's dependence 
on some constant from there - https://github.com/trilbymedia/grav-plugin-tntsearch/issues/13)
- Database backups: pg_dump


External Dependencies on Pipeline:

- http://internal.chatmatic.info/messenger-code (used to resize/prepare messenger code images/zip)
- http://internal.chatmatic.info/count-broadcast (used to determine how many subscribers a broadcast will effect given a set of filters)
- http://internal.chatmatic.info/workflow-json (export the first json step of a workflow)