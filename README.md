# NaranjoIzan_GrandeIvan_DAW2_0613_AA01_ChatOnline

## Archivos y foto de perfil

- En una instalacion nueva, ejecuta `db_watsap.sql`.
- Si ya tienes la base de datos original, ejecuta una vez `db_migration_chat_files.sql` y `db_migration_profile_photo.sql`.
- Si ya existe la columna `profile_image_path`, no vuelvas a ejecutar `db_migration_profile_photo.sql`.
- Si ya existen `message_type`, `file_path` y `original_file_name` en `tbl_messages`, no vuelvas a ejecutar `db_migration_chat_files.sql`.
- En el chat se pueden adjuntar imagenes JPG, PNG, GIF o WEBP, y documentos PDF, TXT, DOC, DOCX, XLS, XLSX, PPT o PPTX.
- El limite de subida es 2 MB, acorde a la configuracion actual de PHP.
- Las fotos de perfil se cambian desde **Mi perfil** y aceptan JPG, PNG, GIF o WEBP.
- Los archivos del chat se guardan en `archivos_chat/`; las fotos, en `fotos_perfil/`.


Aplicacion de whatsapp 