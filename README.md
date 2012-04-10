Moodle Admin Tool Plug In for Adding Feedback to Questions
----------------------------------------------------------

You can batch add correct / partially correct / incorrect feedback to questions in a selected context or category.

This admin tool was written by Jamie Pratt (http://jamiep.org/).

It is compatible with Moodle 2.2+ (the admin tool plug in type was introduced in 2.2)

### Installation

#### Using git 

To install using git for a 2.2+ Moodle installation, type this command in the
root of your Moodle install:

git clone git://github.com/jamiepratt/moodle-admin_tool_questionaddfeedback.git admin/tool/questionaddfeedback

Then add admin/tool/questionaddfeedback to your git ignore.

#### Not using git 

Alternatively, download the the file archive from

* zip - https://github.com/jamiepratt/moodle-admin_tool_questionaddfeedback/zipball/master
* tar.gz - https://github.com/jamiepratt/moodle-admin_tool_questionaddfeedback/tarball/master

Uncompress it into the admin/tool folder, and then rename the new folder to questionaddfeedback.


### Usage

There is no need to upgrade the db with this plug in, just install the code and you can use it straight away.

You will find the plug in tool at the root of your admin menu.

* You can pick any question category or context to add feedback to all questions within that category. Or pick an individual
question.
* Once you have picked which questions you want to apply changes to then you fill in the required feedback.
* You can choose to embed files in the feedback and they will be correctly moved to the questions files area.
* You can chosse to append or prepend new feedback or replace the old combined feedback. However if you choose to append or prepend feedback to existing feedback
 unfortunately any existing files associated with the old feedback will no longer be associated with the question if you do choose
 to append or prepend new feedback to existing feedback that did have some files attached.
 