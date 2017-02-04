/**************************************************************************
Copyright 2017 Benato Denis

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*****************************************************************************/

//create a root user
db.createUser({user:"MongoDB_testing", pwd:"45Jfh4oe8E",roles:["readWrite","dbAdmin"]});

//auth the new user
db.auth("MongoDB_testing", "45Jfh4oe8E");

//and use it to create a new database
db.getSiblingDB("testing");