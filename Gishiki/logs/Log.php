<?php
/****************************************************************************
  Copyright 2015 Benato Denis

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 *******************************************************************************/

namespace Gishiki\Logging {
    
    /**
     * An helper class for storing logs of what happens on the server
     *
     * Benato Denis <benato.denis96@gmail.com>
     */
    class Log {
        private $unix_timestamp;
        private $level;
        private $stackTrace;
        private $shortMessage;
        private $longMessage;

        /**
         * Setup a new log entry
         *
         * @param string $shortMessage
         * @param string $longMessage
         * @param integer $priority
         */
        public function __construct($shortMessage = "", $longMessage = "", $priority = Priority::WARNING) {
            //setup the log
            $this->unix_timestamp = time();
            $this->stackTrace = debug_backtrace();
            $this->shortMessage = $shortMessage;
            $this->longMessage = $longMessage;

            //assign a priority to the current log
            $this->SetPriority($priority);
        }

        /**
         * Change the priority of the current log
         *
         * @param integer $priorityLevel one of the Gishiki\Logging\Priority priority code
        */
        public function SetPriority($priorityLevel) {
            //change the priority level
            $this->level = $priorityLevel;
        }

        /**
         * Set the short message/description of the log entry
         *
         * @param string the short message
         */
        public function SetShortMessage($message) {
            //change the short message
            $this->shortMessage = "".$message;
        }

        /**
         * Get the short message/description of the log entry
         *
         * @param string the long message
         */
        public function SetLongMessage($message) {
            //change the long message
            $this->longMessage = "".$message;
        }

        /**
         * Get the long message/description of the log entry
         *
         * @return string the short message
         */
        public function GetShortMessage() {
            //return the short message
            return $this->shortMessage;
        }

        /**
         * Get the long message/description of the log entry
         *
         * @return string the long message
         */
        public function GetLongMessage() {
            //return the long message
            return $this->longMessage;
        }

        /**
         * Get the timestamp of the log entry
         *
         * @return integer the timestamp
         */
        public function GetTimestamp() {
            //return the timestamp
            return $this->unix_timestamp;
        }

        /**
         * Get the stacktrace of the log entry
         *
         * @return string the timestamp
         */
        public function GetStacktrace() {
            //return the stacktrace serialized
            return json_encode($this->stackTrace);
        }

        /**
         * Get the urgency level of the log entry
         *
         * @return string the level
         */
        public function GetLevel() {
            //return the log level
            return $this->level;
        }

        /**
         * Save the current log entry using syslogd
         */
        public function Save() {
           //use syslog to store the log entry on the current machine
            if (openlog("Gishiki" , LOG_NDELAY | LOG_PID, LOG_USER)) {
                //save the log using the UNIX standard logging ultility
                syslog($this->GetLevel(), "[".$this->GetTimestamp()."] (".$this->GetShortMessage().") ".$this->GetLongMessage()."");
                
                //virtually close the connection to syslogd
                closelog();
            }
        }
    }

}
