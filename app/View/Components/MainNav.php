<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use App\Services\GraphQLService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Constraint\Exception;

class MainNav extends Component
{
    /**
     * Create a new component instance.
     */

    private $graphqlService;

    public $menuItems;

    public function __construct(GraphQLService $graphqlService)
    {
        $this->graphqlService = $graphqlService;
        $this->getMenuItems();
    }

    public function getMenuItems() {

    
        $query = "query {
            menuItems(where: {location: MAIN_NAV, parentDatabaseId: 0}) {
                edges {
                    node {
                    key: id
                    parentId
                    title: label
                    uri
                    childItems(first: 100) {
                        edges {
                            node {
                            key: id
                            parentId
                            title: label
                            uri
                            childItems {
                                edges {
                                node {
                                    key: id
                                    parentId
                                    title: label
                                    uri
                                    openInNewTab {
                                    openInNewTabwindow
                                    }
                                }
                                }
                            }
                        openInNewTab {
                            openInNewTabwindow
                        }
                    }
                  }
                }
                openInNewTab {
                  openInNewTabwindow
                }
              }
            }
          }
         }";

        try {
            $menuItems = $this->graphqlService->executeGraphQLQuery($query);
            $this->menuItems = $menuItems['data']['menuItems'];
            
        } catch (\Exception $e) {
            Log::error("Error in getNews method: " . $e->getMessage());
            // Handle the exception appropriately (e.g., show an error message)
        }


    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(  ): View|Closure|string 
    {

        return view('components.main-nav', [
            'menuItems' => $this->menuItems
        ]);
    }
}
