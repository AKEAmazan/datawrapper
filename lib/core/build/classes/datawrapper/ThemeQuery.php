<?php



/**
 * Skeleton subclass for performing query and update operations on the 'theme' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.datawrapper
 */
class ThemeQuery extends BaseThemeQuery
{

    public function allThemesForUser() {
        $user = DatawrapperSession::getUser();

        $themes = array(ThemeQuery::create()->findPk("default"));

        if ($user->isAdmin()) {
            $allThemes = ThemeQuery::create()->find();

            foreach ($allThemes as $theme) {
                if ($theme->getId() == "default") continue;

                $themes[] = $theme;
            }
        } else {
            $userThemes = UserThemeQuery::create()
                ->filterByUser($user)
                ->find();

            foreach ($userThemes as $theme) {
                $themes[] = $theme->getTheme();
            }

            $organization = $user->getCurrentOrganization();

            if ($organization) {
                $orgThemes = OrganizationThemeQuery::create()
                    ->filterByOrganization($organization)
                    ->find();

                foreach ($orgThemes as $theme) {
                    $themes[] = $theme->getTheme();
                }

            }
        }

        return $themes;
    }

}
