
Kajona V4 lang subsystem.

    Since Kajona V4, it is possible to change the default-lang files by deploying them inside the projects'
    lang-folder.
    This provides a way to change texts and labels without breaking them during the next system-update.

    Example: By default, the Template-Manager is titled "Packagemanagement".
    The entry is created by the file

    /core/module_packagemanager/lang/module_packagemanager/lang_packagemanager_en.php -> $lang["modul_titel"].

    To change the entry to "Packages" or "Modules" copy the original lang-file into the matching folder
    under the project root. Using the example above, that would be:

    /project/lang/module_packagemanager/lang_packagemanager_en.php

    Now change the entry
    $lang["modul_titel"] = "Packagemanagement";
    to
    $lang["modul_titel"] = "Packages";

    Reload your browser and enjoy the relabeled interface.

