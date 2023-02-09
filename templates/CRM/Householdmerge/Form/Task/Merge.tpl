{*-------------------------------------------------------+
| SYSTOPIA Donation Receipts Extension                   |
| Household Merger Extension                             |
| Copyright (C) 2015-2023 SYSTOPIA                       |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*}

{capture assign=ignore_count}{$ignored|@count}{/capture}
{capture assign=ignore_msg}{ts domain="de.systopia.householdmerge" 1=$ignore_count}(%1 selected contacts have been ignored){/ts}{/capture}

<div>
  <h3>{ts domain="de.systopia.householdmerge"}Merge Contacts:{/ts}{if $ignore_count} {$ignore_msg}{/if}</h3>
  <div>
    <table>
    {foreach from=$contacts item=contact}
      <tr class="{cycle values="odd-row,even-row"}">
        <td><div class="icon crm-icon {$contact.contact_type}-icon"></div>&nbsp;{$contact.display_name}</td>
      </tr>
    {/foreach}
    </table>
  </div>
</div>
<br/>
<div>
  <h3>{ts domain="de.systopia.householdmerge"}Into Household:{/ts}</h3>
  <table>
    {foreach from=$households item=household name=hhlist}
    <tr><td colspan=2><hr></td></tr>
    <tr>
      <td>
        <input id="hh_option_existing" class="crm-form-radio" type="radio" name="hh_option" value="selected_{$household.id}" {if $smarty.foreach.hhlist.first}checked="checked"{/if}>
        <label for="hh_option_existing">{ts domain="de.systopia.householdmerge"}<strong>Selected</strong> Household{/ts}</label>
      </td>
      <td>
        <div class="icon crm-icon {$household.contact_type}-icon"></div>&nbsp;{$household.display_name}&nbsp;[{$household.id}]
      </td>
    </tr>
    {/foreach}

    <tr><td colspan=2><hr></td></tr>
    <tr>
      <td>
        <input id="hh_option_new" class="crm-form-radio" type="radio" name="hh_option" value="new" {if not $households}checked="checked"{/if} >
        <label for="hh_option_new">{ts domain="de.systopia.householdmerge"}Create <strong>new</strong> Household{/ts}</label>
      </td>
      <td>
        <div>
          <span>{$form.household_name_pattern.label}</span>
          <span>{$form.household_name_pattern.html}</span>
          <span>{$form.household_name.html}</span>
        </div>
      </td>
    </tr>

    <tr><td colspan=2><hr></td></tr>
    <tr>
      <td>
        <input id="hh_option_existing" class="crm-form-radio" type="radio" name="hh_option" value="existing">
        <label for="hh_option_existing">{ts domain="de.systopia.householdmerge"}Choose other <strong>existing</strong> Household{/ts}</label>
      </td>
      <td>
        <div>
          <span>{$form.existing_household.label}</span>
          <span>{$form.existing_household.html}</span>
        </div>
      </td>
    </tr>
    <tr><td colspan=2><hr></td></tr>
  </table>
</div>

{* contact ID list (hidden) *}
{$form.hh_contacts.html}

<br/>
<h1>{ts domain="de.systopia.householdmerge"}Warning! This cannot be undone!{/ts}</h1>
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script type="text/javascript">
{literal}
// cj("#hh_option_new").val('existing');

cj("#household_name_pattern").change(function(){
  var selected_pattern = cj("#household_name_pattern").val();
  if (selected_pattern == "custom") {
    cj("#household_name").enable(true);
  } else {
    cj("#household_name").enable(false);
  }
});
{/literal}
</script>
