@webUI
Feature: admin sharing settings
As a admin
I want to be able to manage sharing settings ownCloud server
So that I can enable, disable, allow or restrict different sharing behaviour

	Background:
			Given the admin has browsed to the admin sharing settings page

	Scenario: disable sharing API
			When the admin disables the share API using the webUI
			And the user retrieves the capabilities using the API
			Then the capabilities should contain
				| capability    | path_to_element | value |
				| files_sharing | api_enabled     | EMPTY |

	Scenario: disable public sharing
			When the admin disables share via link using the webUI
			And the user retrieves the capabilities using the API
			Then the capabilities should contain
				| capability    | path_to_element  | value |
				| files_sharing | public@@@enabled | EMPTY |

	Scenario: disable public upload
			When the admin disables public uploads using the webUI
			And the user retrieves the capabilities using the API
			Then the capabilities should contain
				| capability    | path_to_element | value |
				| files_sharing | public@@@upload | EMPTY |
