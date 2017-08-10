<?php

/*
 * VISUALIZE STEP
 */
$app->get('/(chart|map)/:id/visualize', function ($id) use ($app) {
    disable_cache($app);

    check_chart_writable($id, function($user, $chart) use ($app) {
        $visData = "";

        $allThemes = ThemeQuery::create()->allThemesForUser();
        $themeMeta = [];

        foreach ($allThemes as $theme) {
            $themeMeta[] = array(
                "id" => $theme->getId(),
                "title" => $theme->getTitle(),
                "data" => $theme->getThemeData()
            );
        }

        try {
            $allVis = array();

            foreach (DatawrapperVisualization::all() as $vis) {
                unset($vis['options']['basemap']);
                unset($vis['icon']);
                $allVis[$vis['id']] = $vis;
            }

            $visData = json_encode(array(
                'visualizations' => $allVis,
                'vis' => DatawrapperVisualization::get($chart->getType()),
                'themes' => $themeMeta,
            ));
        } catch (Exception $e) {
            error('io-error', $e->getMessage());
        }

        $vis = DatawrapperVisualization::get($chart->getType());
        parse_vis_options($vis);

        $theme = ThemeQuery::create()->findPk($chart->getTheme());

        if (empty($theme)) {
            $theme = ThemeQuery::create()->findPk("default");
        }

        $page = array(
            'title' => $chart->getID() . ' :: '.__('Visualize'),
            'chartData' => $chart->loadData(),
            'chart' => $chart,
            'visualizations_deps' => DatawrapperVisualization::all('dependencies'),
            'visualizations' => DatawrapperVisualization::all(),
            'vis' => $vis,
            'themes' => $themeMeta,
            'theme' => $theme,
            'type' => $chart->getNamespace(),
            'debug' => !empty($GLOBALS['dw_config']['debug_export_test_cases']) ? '1' : '0',
            'vis_data' => $visData
        );
        add_header_vars($page, $chart->getNamespace());
        add_editor_nav($page, 3, $chart->getNamespace());

        if (!$chart->isDataWritable($user)) {
            $page['steps'][0]['readonly'] = true;
            $page['steps'][1]['readonly'] = true;
        }

        $app->render('chart/visualize.twig', $page);
    });
});

