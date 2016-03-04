<?php
/**
 * TinyQueries - Framework for merging and nesting relational data
 *
 * @author      Wouter Diesveld <wouter@tinyqueries.com>
 * @copyright   2012 - 2015 Diesveld Query Technology
 * @link        http://www.tinyqueries.com
 * @version     3.0.6
 * @package     TinyQueries
 *
 * License
 *
 * This software is licensed under Apache License 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
require_once( dirname(__FILE__) . '/../libs/TinyQueries/TinyQueries.php' );
	
$api = new TinyQueries\AdminApi( dirname(__FILE__) . '/../config/config.xml' );

$api->sendResponse();

