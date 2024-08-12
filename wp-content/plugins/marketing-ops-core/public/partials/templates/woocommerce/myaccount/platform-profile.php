<?php
/**
 * This file is used for templating the platform profile registration for loggedin members.
 *
 * @since 1.0.0
 * @package Marketing_Ops_Core
 * @subpackage Marketing_Ops_Core/public/partials/templates/woocommerce/myaccount
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>
<form>
    <section class="agencyformone">
        <h1>General</h1>
        <div class="agencyformgroup">
            <label>Platform name <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
            <input type="text" class="agancyinputbox" id="agencyname" name="agencyname">
            <small>This will be how your name will be displayed in the account section</small>
        </div> 

        <div class="agencyformgroup logoupload">
            <label>Logo <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
            <div class="upload-btn-wrapper">
                <button class="btn">Select an image</button>
                <p>For the best results, upload horizontal version, 560 x 240px max</p>
                <input type="file" name="myfile" />
            </div>
        </div>

        <div class="agencyformgroup">
            <label>Description </label>
            <textarea id="description" name="description" rows="4" cols="50"></textarea>
            <small>0 of 400 max character</small>    
        </div>

        <h2>Contact</h2>
        <div class="agencyformgroups">
            <div class="agencyfirstblock">
                <label>Name</label>
                <input type="text" class="agancyinputbox" id="name" name="name">
            </div> 
            <div class="agencyfirstblock">
                <label>E-mail</label>
                <input type="email" class="agancyinputbox" id="email" name="email">
            </div>    
        </div>
        <div class="agencyformgroup">
            <label>Agency Website <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
            <input type="text" class="agancyinputbox" id="agencywebsite" name="agencywebsite">
        </div>
    </section>

    <section class="agencyformone detailsblock">
        <h3>Details</h3>
        <h5>Type of the Platform</h5>
        <p>
            <input type="radio" id="affiliate" name="radio-group" checked>
            <label for="affiliate">Affiliate
            </label>
        </p>
        <p>
            <input type="radio" id="consultant" name="radio-group">
            <label for="consultant">Amazon
            </label>
        </p>
        <p>
            <input type="radio" id="digital" name="radio-group">
            <label for="digital">Branded Content
            </label>
        </p>
        <p>
            <input type="radio" id="holding" name="radio-group">
            <label for="holding">Call Tracking
            </label>
        </p>
        <p>
            <input type="radio" id="influencer" name="radio-group">
            <label for="influencer">Influencer
            </label>
        </p>
        <p>
            <input type="radio" id="PR" name="radio-group">
            <label for="PR">Lead Cen
            </label>
        </p>
        <p>
            <input type="radio" id="Reddit" name="radio-group">
            <label for="Reddit">Publisher Tech
            </label>
        </p>
        <p>
            <input type="radio" id="Search" name="radio-group">
            <label for="Search">SMS
            </label>
        </p>
        <div class="agencyformgroups">
            <div class="agencyfirstblock">
                <label>Year founded</label>
                <select>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>
            </div> 
            <div class="agencyfirstblock">
                <label>Employees</label>
                <select>
                    <option value="1">Option 1</option>
                    <option value="2">Option 2</option>
                    <option value="3">Option 3</option>
                </select>
            </div>    
        </div>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="APAC">
            <label for="APAC">APAC</label>
        </div>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="northamerica">
            <label for="northamerica">North America</label>
        </div>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="US">
            <label for="US">US</label>
        </div>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="EMEA">
            <label for="EMEA">EMEA</label>
        </div>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="southamerica">
            <label for="southamerica">South America</label>
        </div>
        <button class="addregion">Add another region</button>

        <div class="agencyformgroups">
            <div class="agencyfirstblock">
                <label>Primary Verticals <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Automotive">
                    <label for="Automotive">Automotive</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="ConsumerPackageGoods">
                    <label for="ConsumerPackageGoods">Consumer Packaged Goods</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Education">
                    <label for="Education">Education</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="FinancialServices">
                    <label for="FinancialServices">Financial Services</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Healthcare">
                    <label for="Healthcare">Healthcare</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Retail">
                    <label for="Retail">Retail</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Subscription">
                    <label for="Subscription">Subscription</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Travel">
                    <label for="Travel">Travel</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="B2B">
                    <label for="B2B">B2B</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="DTC">
                    <label for="DTC">DTC</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Entertainment">
                    <label for="Entertainment">Entertainment</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Caming">
                    <label for="Caming">Caming</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Real Estate">
                    <label for="Real Estate">Real Estate</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="SMB">
                    <label for="SMB">SMB</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Telecom">
                    <label for="Telecom">Telecom</label>
                </div>
                <button class="addregion">Add new vertical</button>
            </div>


            <div class="agencyfirstblock">
                <label>What services do you provide? <i><svg xmlns="http://www.w3.org/2000/svg" width="9" height="21" viewBox="0 0 9 21" fill="none"><path d="M3.2 6H5.45L5.325 8.675L7.55 7.275L8.675 9.175L6.3 10.425L8.65 11.725L7.525 13.6L5.325 12.175L5.425 14.7H3.175L3.325 12.2L1.2 13.55L0.075 11.65L2.325 10.4L0 9.125L1.15 7.225L3.325 8.6L3.2 6Z" fill="url(#paint0_linear_34_3644)"/><defs><linearGradient id="paint0_linear_34_3644" x1="-0.204631" y1="10.371" x2="13.798" y2="10.371" gradientUnits="userSpaceOnUse"><stop stop-color="#FD4B7A"/><stop offset="1" stop-color="#4D00AE"/></linearGradient></defs></svg></i></label>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Affiliate Marketing">
                    <label for="Affiliate Marketing">Affiliate Marketing</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Database Acquisition">
                    <label for="Database Acquisition">Database Acquisition</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Influencer Marketing">
                    <label for="Influencer Marketing">Influencer Marketing</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Programmatic">
                    <label for="Programmatic">Programmatic</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="SEO">
                    <label for="SEO">SEO</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Branded Content">
                    <label for="Branded Content">Branded Content</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Email Marketing">
                    <label for="Email Marketing">Email Marketing</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Performance PR">
                    <label for="Performance PR">Performance PR</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="SEM">
                    <label for="SEM">SEM</label>
                </div>
                <div class="agencyformgroup form-group">
                    <input type="checkbox" id="Social media">
                    <label for="Social media">Social media</label>
                </div>
                <button class="addregion">Add new service</button>
            </div>
        </div>
    </section>

    <section class="agencyformone mentions">
        <h6 class="special">Special mentions</h6>
        <div class="agencyformgroup videogroup articals" style="margin-top:0;">
        <h5>Testimonial</h5>
            <label>Text </label>
            <textarea id="Text" name="Text" rows="4" cols="50"></textarea>
            <small>0 of 400 max character</small>    
        </div>

        <div class="agencyformgroup person">
            <label>Name of the person quoted </label>
            <input type="text" class="agancyinputbox" id="person" name="person">
        </div>

        <div class="agencyformgroup videogroup articals">
        <h5>Clients</h5>
            <label>Text </label>
            <textarea id="Text" name="Text" rows="4" cols="50"></textarea>
            <small> list as many as you want</small>    
        </div>

        <div class="agencyformgroup videogroup articals">
        <h5>Certifications</h5>
            <label>Text </label>
            <textarea id="Text" name="Text" rows="4" cols="50"></textarea>
            <small> list as many as you want</small>    
        </div>

        <div class="agencyformgroup videogroup articals">
        <h5>Awards</h5>
            <label>Text </label>
            <textarea id="Text" name="Text" rows="4" cols="50"></textarea>
            <small> Please list one award per line to create a list</small>    
        </div>
        
        <div class="agencyformgroup videogroup">
        <h6 class="jbtitle">Video</h6>
            <label>Youtube / Vimeo link </label>
            <input type="text" class="agancyinputbox" id="Video" name="Video">
        </div>

        <h6 class="jbtitle">Articles & Press Releses</h6>
        <div class="agencyformgroup form-group">
            <input type="checkbox" id="jobs">
            <label for="jobs">Include articles & press releases posted by me to this page</label>
        </div>
    </section> 

    <section class="agencyformone">
        <ul>
            <li>
                <a href="javascript:void(0);" class="savedratbtn">
                    Save Draft
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="profilebtn">
                    Create Profile
                </a>
            </li>
        </ul>
    </section>
</form>