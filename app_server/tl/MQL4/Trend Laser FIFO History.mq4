//*------------------------------------------------------------------*
//|                                  Strength Meter FIFO History.mq4 |
//|                                         Developed by Coders Guru |
//|                                            Copyrighted for Aaron |
//|                                            http://www.xpworx.com |
//*------------------------------------------------------------------*
#property copyright "Aaron"
#property link      "http://www.xpworx.com"
#property description "Strength Meter FIFO History"
#property version     "1.00"
#property strict
//*------------------------------------------------------------------*
//Last Modified: 2015.07.24
//*------------------------------------------------------------------*
#import "xpPost.dll"
   string  xpPost(string Page, string Post);											  			   		
   void SetURL(string URL, string Pass_Key);
   int xpUploadFile(string file_name);
#import
//*------------------------------------------------------------------*
enum YN {No,Yes};
//*------------------------------------------------------------------*
extern 		string  		         •••FIFO_Settings•••              = "••••••••••••••••••••••••";
extern      string               URL                              = "http://www.topforexsignal.com/pipsguy/tl";
extern      string               PassKey                          = "xpworx2015";
extern      int                  BarsCount                        = 1000;
extern      YN                   Grab_M1_Data                     = No;
extern      int                  D1_BarsCount                     = 100;
extern      int                  W1_BarsCount                     = 100;
extern      int                  MN1_BarsCount                    = 100;
extern      int                  UpdateInterval                   = 15;

extern 		string  		         •••Trend_Laser_Settings•••       = "••••••••••••••••••••••••";
input       int                  TrendPeriod                      = 3;      //Period
//*------------------------------------------------------------------*
#define     IndiName    "PipFinite Trend Laser 2.3_source"
string      Page = URL + "/tl.php";
//*------------------------------------------------------------------*
bool   UseWebPost = true;
//*------------------------------------------------------------------*
datetime    LastUpdateTime;
datetime    LastRunTime;
//*------------------------------------------------------------------*
#define BullishTrend       0
#define BearishTrend       1  
#define BuySignal          2   
#define SellSignal         3 
#define AverageProfit      4   
#define MinimumMove        5    
#define TIME               6
//*------------------------------------------------------------------*
#define BullishTrendBuf    0
#define BearishTrendBuf    1  
#define BuySignalBuf       14   
#define SellSignalBuf      15 
#define AverageProfitBuf   16
#define MinimumMoveBuf     17    
//*------------------------------------------------------------------*
#define FOLDER   "TL\\"
#define FIFO      "1"
#define UPDADTED  "2"
//*------------------------------------------------------------------*
int pause = 100;
//*------------------------------------------------------------------*
int OnInit()
{ 
   if(!IsDllsAllowed()) 
   {
      Alert("DLL is not enabled. You have to enable it to upload to server.");   
      return(0);
   }
   
   SetURL(URL,PassKey);   
   
   RunScript();
   LastRunTime = TimeCurrent();
         
   return(0);
}
//*------------------------------------------------------------------*
void OnDeinit(const int reason)
{
   Comment("");
}
//*------------------------------------------------------------------*
void OnTick()
{ 
   if(!IsDllsAllowed()) 
   {
      Comment("DLL is not enabled. You have to enable it to upload to server.");   
      return;
   }
   
   //Comment(GetTrendLaser(PERIOD_CURRENT,0,0),":",GetTrendLaser(PERIOD_CURRENT,1,0));
   //return;

   
   if(TimeCurrent()>LastRunTime+(UpdateInterval*40))
   {
      RunScript();
      LastRunTime = TimeCurrent();
      return;
   }
   
   if(TimeCurrent()>LastUpdateTime+UpdateInterval)
   {
      LastUpdateTime = TimeCurrent();
      
      if(Grab_M1_Data)
      {
         PostData(PERIOD_M1);
         Sleep(pause);      
      }
      
      PostData(PERIOD_M5);
      Sleep(pause);       
      PostData(PERIOD_M15);
      Sleep(pause);      
      PostData(PERIOD_M30);
      Sleep(pause);      
      PostData(PERIOD_H1);
      Sleep(pause);      
      PostData(PERIOD_H4);
      Sleep(pause);      
      PostData(PERIOD_D1);
      Sleep(pause);      
      PostData(PERIOD_W1);
      Sleep(pause);      
      PostData(PERIOD_MN1);      
      
      Comment("Last update: ", TimeToString(TimeCurrent()));
   }
}
//*------------------------------------------------------------------*
void RunScript()
{   

//--------------------------------
// PROCESSING MN1 DATA
//--------------------------------
   DoIt(PERIOD_MN1, MN1_BarsCount); 
   Sleep(pause);

//--------------------------------
// PROCESSING W1 DATA
//--------------------------------
   DoIt(PERIOD_W1, W1_BarsCount); 
   Sleep(pause);  
   
//--------------------------------
// PROCESSING D1 DATA
//--------------------------------
   DoIt(PERIOD_D1, D1_BarsCount); 
   Sleep(pause);
   
//--------------------------------
// PROCESSING H4 DATA
//--------------------------------
   DoIt(PERIOD_H4, BarsCount); 
   Sleep(pause);
   
//--------------------------------
// PROCESSING H1 DATA
//--------------------------------
   DoIt(PERIOD_H1, BarsCount); 
   Sleep(pause);
   
//--------------------------------
// PROCESSING M30 DATA
//--------------------------------
   DoIt(PERIOD_M30, BarsCount); 
   Sleep(pause);
   
//--------------------------------
// PROCESSING M15 DATA
//--------------------------------
   DoIt(PERIOD_M15, BarsCount); 
   Sleep(pause);
   
//--------------------------------
// PROCESSING M5 DATA
//--------------------------------
   DoIt(PERIOD_M5, BarsCount); 
   Sleep(pause);

//--------------------------------
// PROCESSING M1 DATA
//--------------------------------
   if(Grab_M1_Data)
   {
      DoIt(PERIOD_M1, BarsCount); 
      Sleep(pause);
   }

   Comment("Done!");
   
}
//*------------------------------------------------------------------*
string GetTrendLaser(int tf, int buf, int bar=0)
{
   int back = BarsCount;
   if(tf==PERIOD_D1) back = D1_BarsCount;
   if(tf==PERIOD_W1) back = W1_BarsCount;
   if(tf==PERIOD_MN1) back = MN1_BarsCount;   
   double val = iCustom(Symbol(),tf,IndiName,"",TrendPeriod,back,"",False,False,5,15,8,"",0,"",False,False,False,buf,bar);   
   return(DoubleToString(val));
}
//*------------------------------------------------------------------*
void GetVals(ENUM_TIMEFRAMES tf, string& back[])
{
   back[BullishTrend] = GetTrendLaser(tf, BullishTrendBuf);
   back[BearishTrend] = GetTrendLaser(tf, BearishTrendBuf);
   back[BuySignal] = GetTrendLaser(tf, BuySignalBuf);
   back[SellSignal] = GetTrendLaser(tf, SellSignalBuf);
   back[AverageProfit] = GetTrendLaser(tf, AverageProfitBuf);
   back[MinimumMove] = GetTrendLaser(tf, MinimumMoveBuf);
   back[TIME] = TimeToString(iTime(Symbol(),tf,0));
}
//*------------------------------------------------------------------*
void PostData(ENUM_TIMEFRAMES timeframe)
{    
   string vals[7];
   GetVals(timeframe, vals);
   
   string post = StringConcatenate("pair=", Symbol(), "&tf=", PeriodToText(timeframe), "&bull=", vals[BullishTrend], "&bear=", vals[BearishTrend], 
   "&buy=", vals[BuySignal],"&sell=", vals[SellSignal], "&av=", vals[AverageProfit], "&mm=", vals[MinimumMove], "&time=", vals[TIME]);
      
   string result;
   if(UseWebPost) result = WebPost(Page,post);
   else result = xpPost(Page,post);
   
   if(result==UPDADTED)
   {
      Print("UPDADTED ",PeriodToText(timeframe));
   }
   else
   {
      Print("Result = ", result);
   }
}
//*------------------------------------------------------------------*
int DoIt(int Timeframe, int bars_count)
{
   int uploadResult;
   string data, filename, postResult;

   //Generate the filename
   filename = Symbol() + "_" + PeriodToText(Timeframe);
   
   //Get 1K Bars
   data = Get1KBars(Timeframe, bars_count);  
   Comment("Successfully got ",Symbol()," ",PeriodToText(Timeframe)," 1K Bars data");
   Print("Successfully got ",Symbol()," ",PeriodToText(Timeframe)," 1K Bars data");
//---------------------->>

   //Saving data to local file
   SaveData(filename,data);
   Comment("File ",Symbol(),"_",PeriodToText(Timeframe),".sql"," has been saved");
   Print("File ",Symbol(),"_",PeriodToText(Timeframe),".sql"," has been saved");
//---------------------->>

   //Upload
   uploadResult = UploadFile(Timeframe); 
   if(uploadResult==0) 
   {
   Comment("File ",Symbol(),"_",PeriodToText(Timeframe),".sql"," uploaded to the server");
   Print("File ",Symbol(),"_",PeriodToText(Timeframe),".sql"," uploaded to the server");
   }
   else 
   { 
   Comment("Error uploading ",Symbol(),"_",PeriodToText(Timeframe),".sql"," to the server"); 
   Print("Error uploading ",Symbol(),"_",PeriodToText(Timeframe),".sql"," to the server"); 
   return(0); 
   }
//---------------------->>
   //Run the script
   if(UseWebPost) postResult = WebPost(URL+"/run.php","filename="+filename+".sql");
   else postResult = xpPost(URL+"/run.php","filename="+filename+".sql");
   
   if(postResult=="0") 
   {
   Comment("Successfully inserted ",Symbol(),"_",PeriodToText(Timeframe)," to the database");
   Print("Successfully inserted ",Symbol(),"_",PeriodToText(Timeframe)," to the database"); 
   }
   else 
   { 
   Print(postResult);
   Comment("Error inserting ",Symbol(),"_",PeriodToText(Timeframe)," to the database"); 
   Print("Error inserting ",Symbol(),"_",PeriodToText(Timeframe)," to the database"); 
   return(0); 
   }
//---------------------->>   
   return(1);
}
//*------------------------------------------------------------------*
string Get1KBars(int Timeframe, int bars_count)
{
   string line, data;  
   
   data = CreateHeader(Symbol()+"_"+PeriodToText(Timeframe));
  
   for(int cnt=0; cnt<bars_count; cnt++)
   {
      line = StringConcatenate("INSERT INTO `",Symbol(),"_",PeriodToText(Timeframe),"`(bull,bear,buy,sell,av,mm,time) VALUES(",
      "'",GetTrendLaser(Timeframe,BullishTrendBuf,cnt),"',",
      "'",GetTrendLaser(Timeframe,BearishTrendBuf,cnt),"',",
      "'",GetTrendLaser(Timeframe,BuySignalBuf,cnt),"',",
      "'",GetTrendLaser(Timeframe,SellSignalBuf,cnt),"',",      
      "'",GetTrendLaser(Timeframe,AverageProfitBuf,cnt),"',",
      "'",GetTrendLaser(Timeframe,MinimumMoveBuf,cnt),"',",
      "'",TimeToString(iTime(Symbol(),Timeframe,cnt)),"'",");");
      data = data + line + "\n";
   }
   return(data);
}
//*------------------------------------------------------------------*
string CreateHeader(string filename)
{  
   string text = "DROP TABLE IF EXISTS `" + filename + "`;\n";
   text += "CREATE TABLE IF NOT EXISTS `" + filename + "`(\n";
   text += "`id` int(5) NOT NULL,\n";
   text += "`bull` varchar(10) NOT NULL,\n";
   text += "`bear` varchar(10) NOT NULL,\n";
   text += "`buy` varchar(10) NOT NULL,\n";
   text += "`sell` varchar(10) NOT NULL,\n";   
   text += "`av` varchar(10) NOT NULL,\n";
   text += "`mm` varchar(10) NOT NULL,\n";
   text += "`time` varchar(30) NOT NULL);\n";
   text += "ALTER TABLE `" + filename + "` ADD PRIMARY KEY (`id`);\n";
   text += "ALTER TABLE `" + filename + "` MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;\n";
   return(text);
}
//*------------------------------------------------------------------*
bool SaveData(string filename, string data)
{
   filename = FOLDER + filename+".sql";
   int handle=FileOpen(filename, FILE_TXT|FILE_WRITE);
   if(handle!=INVALID_HANDLE)
   {
      FileWrite(handle, data);
      FileClose(handle);
      return(true);
   }
   return(false);
}
//*------------------------------------------------------------------*
int UploadFile(int Timeframe)
{
	string FileName = FOLDER + Symbol()+"_"+PeriodToText(Timeframe)+".sql";
	int result = xpUploadFile(GetFilesPath()+"\\"+FileName);
	if(result!=0)
	{
	   Print("Can not upload the file (error: #"+IntegerToString(result)+")");
	   return(-1);
	}
	else
	{
	   Print("File (",FileName,") uploaded successfully!");
	}
   return(0);
}
//*------------------------------------------------------------------*
string GetFilesPath()
{
   string full_path = MQLInfoString(MQL_PROGRAM_PATH);
   int pos = StringFind(full_path,"\\MQL4\\");
   if(pos==-1) return("");
   string path = StringSubstr(full_path,0,pos+6);
   return(path+"Files\\");
   return("");
}
//*------------------------------------------------------------------*
string WebPost(string url, string post)
{
   string cookie=NULL,  headers;
   char result[], data[];
      
   ArrayResize(data,StringToCharArray(post,data,0,WHOLE_ARRAY,CP_UTF8)-1);

   ResetLastError();
   
   int timeout=5000;
   int res=WebRequest("POST",url,cookie,NULL,timeout,data,0,result,headers);

   if(res!=200)
   {
      Print("Error in WebRequest. Error code  =",GetLastError());
      Print("Add the address '"+url+"' in the list of allowed URLs on tab 'Expert Advisors'");
   }
   else
   {
      return(CharArrayToString(result));
   }
   return("-1");
}
//*------------------------------------------------------------------*
void GetTradeSymbol(string& TradeSymbol) 
{ 
   if(TradeSymbol=="") TradeSymbol=Symbol(); 
}
//*------------------------------------------------------------------*
double GetPoint(string TradeSymbol = "")
{
   GetTradeSymbol(TradeSymbol);
   if(StringFind(TradeSymbol,"XAU")>-1 || StringFind(TradeSymbol,"xau")>-1) return(0.1);  //Gold
   if(MarketInfo(TradeSymbol,MODE_DIGITS)==2 || MarketInfo(TradeSymbol,MODE_DIGITS)==3) return(0.01);
   if(MarketInfo(TradeSymbol,MODE_DIGITS)==4 || MarketInfo(TradeSymbol,MODE_DIGITS)==5) return(0.0001);
   if(MarketInfo(TradeSymbol,MODE_DIGITS)==0) return(1); //Indexes
   return(0.0001);
}
//*------------------------------------------------------------------*
void MLP(string title, string value="", int op=0, color clr=clrPurple,int print_corner=0)
{
   static int line=0;
   line++;
   if(title=="reset") {line=0; return;} //reset
   
   int col1=1, col2=6;
   
   if(op==-1) //CLEAR
   {
      DelPrints(true);   
      CPrint(1,col1,title,clr);
      CPrint(1,col2,value,clr);
      line=0; 
      return;
   } 
   if(StringLen(title)==0) { CPrint(line,col1,"",clr); CPrint(line,col2,"",clr,print_corner); return;}
   if(StringLen(title)==1) {string t=title; for(int c=0; c<60; c++) title=title+t; CPrint(line,col1,title,clr,print_corner); return;}
   
   if(op==1) {if(value==DoubleToStr(OP_BUY,0)) value="Buy"; else if(value==DoubleToStr(OP_SELL,0)) value="Sell"; else value="NA";} //Buy-Sell
   if(op==2) {if(value==DoubleToStr(true,0)) value="Yes"; else value="No";} //Yes-No   
   if(op==3) //remove zeros
   {
      string tmp=value;
      for(int cnt=1;cnt<StringLen(value);cnt++)
      {
         if(StringGetChar(value,StringLen(value)-cnt)==48)
         tmp = StringSubstr(value,0,StringLen(value)-cnt);
         else break;
      }
      if(StringGetChar(tmp,StringLen(tmp)-1)=='.') tmp=tmp+"0";
      value=tmp;
   }   
   if(op==4) value = DoubleToStr(StrToDouble(value),Digits); //digit   
   if(op==5) if(StrToDouble(value)==EMPTY_VALUE) value="Empty"; //EMPTY_VALUE
   else value = DoubleToStr(StrToDouble(value),Digits);
   
   if(print_corner==0)
   {
      CPrint(line,col1,title,clr,print_corner);
      CPrint(line,col2,value,clr,print_corner);
   }
   else
   {
      CPrint(line,col2,title,clr,print_corner);
      CPrint(line,col1,value,clr,print_corner);   
   }
}
//*------------------------------------------------------------------*
#define PRT_NAME "CP_"
void CPrint(int line,int column,string msg,color font_color=Yellow ,int print_corner=0, int font_size=8, 
string font_name="Arial Bold",int x=15, int y=25 , int xd=40 , int yd=15)
{
   string objName = PRT_NAME + IntegerToString(line) + "_" + IntegerToString(column);
   if(ObjectFind(objName)>-1)
   {
      if(ObjectGetString(0,objName,OBJPROP_TEXT)!=msg)
      {
         ObjectSetText(objName,msg,font_size,font_name,font_color);
         WindowRedraw();
      }
   }
   else
   {
      ObjectCreate(objName,OBJ_LABEL,0,0,0);
      ObjectSetText(objName,msg,font_size,font_name,font_color);
      ObjectSet(objName,OBJPROP_CORNER,print_corner); //Right align
      ObjectSet(objName,OBJPROP_XDISTANCE, x + (column-1)*xd);
      if(line == 1) ObjectSet(objName,OBJPROP_YDISTANCE,y);
      else ObjectSet(objName,OBJPROP_YDISTANCE,y+(yd*(line-1)));
   }
}  
//*------------------------------------------------------------------*
void DelPrints(bool keep_first_line=false)
{
   int cnt;
   if(keep_first_line)
   {
      for(cnt=ObjectsTotal()-1;cnt>=0;cnt--) if(StringFind(ObjectName(cnt),PRT_NAME,0)>-1 && StringFind(ObjectName(cnt),PRT_NAME+"1_",0)==-1)
         ObjectDelete(ObjectName(cnt));
      for(cnt=ObjectsTotal()-1;cnt>=0;cnt--) if(StringFind(ObjectName(cnt),PRT_NAME,0)>-1 && StringFind(ObjectName(cnt),PRT_NAME+"1_",0)==-1)
         ObjectDelete(ObjectName(cnt));
   }
   else
   {
      for(cnt=ObjectsTotal()-1;cnt>=0;cnt--) if(StringFind(ObjectName(cnt),PRT_NAME,0)>-1) ObjectDelete(ObjectName(cnt));
      for(cnt=ObjectsTotal()-1;cnt>=0;cnt--) if(StringFind(ObjectName(cnt),PRT_NAME,0)>-1) ObjectDelete(ObjectName(cnt));
   }
   WindowRedraw();
}
//*------------------------------------------------------------------*
string PeriodToText(int period)
{
   switch (period)
   {
      case 1:
            return("M1");
            break;
      case 5:
            return("M5");
            break;
      case 15:
            return("M15");
            break;
      case 30:
            return("M30");
            break;
      case 60:
            return("H1");
            break;
      case 240:
            return("H4");
            break;
      case 1440:
            return("D1");
            break;
      case 10080:
            return("W1");
            break;
      case 43200:
            return("MN1");
            break;
   }
   
   return("");
}
//*------------------------------------------------------------------*








